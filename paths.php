<?php

require_once __DIR__ . '/../github-core/GitHubClient.php';

GitHubClient::checkGitignore();
$config = GitHubClient::loadConfig();

function parseArgs(array $argv): array
{
    $result = [
        'repo' => null,
        'limit' => 20,
        'sort' => 'views'
    ];
    
    $i = 1;
    while ($i < count($argv)) {
        $arg = $argv[$i];
        
        if (in_array($arg, ['--repo', '-r']) && isset($argv[$i + 1])) {
            $result['repo'] = $argv[++$i];
        } elseif (in_array($arg, ['--limit', '-l']) && isset($argv[$i + 1])) {
            $result['limit'] = (int)$argv[++$i];
        } elseif (in_array($arg, ['--sort', '-s']) && isset($argv[$i + 1])) {
            $result['sort'] = $argv[++$i];
        }
        $i++;
    }
    
    return $result;
}

$args = parseArgs($argv);
$repoFullName = GitHubClient::getRepoFromConfig($config, $args['repo']);

$client = new GitHubClient(
    $config['token'],
    $repoFullName,
    $args['repo'] ?? $config['default_repo'] ?? null
);

function getPathsData(GitHubClient $client, string $sortBy = 'views'): array
{
    $data = $client->request("/repos/{$client->getRepo()}/traffic/popular/paths");
    
    $result = [];
    foreach ($data ?? [] as $item) {
        $result[] = [
            'path' => $item['path'],
            'title' => $item['title'] ?? '',
            'views' => $item['count'],
            'unique' => $item['uniques']
        ];
    }
    
    usort($result, function($a, $b) use ($sortBy) {
        return $b[$sortBy] <=> $a[$sortBy];
    });
    
    return $result;
}

$paths = getPathsData($client, $args['sort']);

if ($args['limit'] !== null && $args['limit'] > 0) {
    $paths = array_slice($paths, 0, $args['limit']);
}

$reportPath = GitHubClient::createReportDir();
$timestamp = GitHubClient::getFileTimestamp();

$title = "Популярные страницы: {$client->getRepo()}";

GitHubClient::saveCsv($paths, "$reportPath/github_pages_$timestamp.csv");
GitHubClient::saveMarkdown($paths, "$reportPath/github_pages_$timestamp.md", $title);

echo "\n  Папка отчёта: github_reports/" . basename($reportPath) . "\n";
echo "  Репозиторий: {$client->getRepo()}\n";
echo "  Сортировка: {$args['sort']}\n\n";

echo "  Топ {$args['limit']} страниц:\n";
foreach ($paths as $i => $path) {
    $displayPath = strlen($path['path']) > 50 ? substr($path['path'], 0, 47) . '...' : $path['path'];
    printf(
        "    %2d. %-50s %5d (%d unique)\n",
        $i + 1,
        $displayPath,
        $path['views'],
        $path['unique']
    );
}

$totalViews = array_sum(array_column($paths, 'views'));
$totalUnique = array_sum(array_column($paths, 'unique'));

echo "\n  Итого: $totalViews просмотров, $totalUnique уникальных\n";
echo "\n  Создано файлов в github_reports/" . basename($reportPath) . "/\n";
