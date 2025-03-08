# IJTER Configuration System

## Overview
This project uses a modern configuration system with environment variables.

## Configuration Files
- `.env`: Environment-specific variables
- `config/config.php`: Main configuration file
- `bootstrap.php`: Application initialization

## How to Use

### In Entry Points
```php
// Load the application
$app = require_once __DIR__ . '/bootstrap.php';

// Access configuration
$siteUrl = $app['config']['app']['url'];

// Access database connection
$pdo = $app['pdo'];

// Use controllers
$papers = $app['paperController']->getAllPublishedPapers();
# IJTER Project
This is the International Journal of Technology and Emerging Research (IJTER) project.
