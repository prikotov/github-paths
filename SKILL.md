---
name: github-paths
description: Популярные страницы репозиториев из GitHub
---

## Когда использовать

- Анализ популярных файлов
- Какие страницы просматривают
- Контент с наибольшим интересом

## Запуск

```bash
php .opencode/skills/github-paths/paths.php [опции]
```

### Опции

| Опция | Сокращение | Описание | Значения | По умолчанию |
|-------|------------|----------|----------|--------------|
| `--repo` | `-r` | Репозиторий из конфига | имя или owner/repo | default_repo |
| `--limit` | `-l` | Лимит записей | число | 20 |
| `--sort` | `-s` | Поле сортировки | `views`, `unique` | `views` |

### Примеры

```bash
# Топ-20 популярных страниц
php .opencode/skills/github-paths/paths.php

# Топ-50 для конкретного репозитория
php .opencode/skills/github-paths/paths.php --repo my-project -l 50

# Сортировка по уникальным посетителям
php .opencode/skills/github-paths/paths.php --sort unique
```

## Результат

`github_reports/YYYY-MM-DD/`:
- `github_pages_YYYY-MM-DD_HH-MM-SS.csv` / `.md`

### Поля в отчёте

| Поле | Описание |
|------|----------|
| `path` | Путь к файлу/странице |
| `title` | Заголовок (если есть) |
| `views` | Просмотров |
| `unique` | Уникальные посетители |
