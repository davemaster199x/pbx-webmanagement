# PBX Web Management System

A web-based management interface for PBX systems with JSONRPC integration.

## System Overview

This project provides a web management interface for PBX systems, featuring:
- API-based configuration management
- JSONRPC client integration
- Secure authentication system
- Audit logging capabilities

## Configuration Structure

### Server Configuration
```php
$config_server = [
    'paths' => [
        'functions' => '/path/to/functions.d',
        'classes' => '/path/to/classes.d',
        'audit_log' => '/path/to/audit_log'
    ],
    'api_keys' => [...],
    'db' => [...],
    'api' => [...]
];
```

### Client Configuration
```php
$config_client = [
    'formats' => [
        'date' => 'n/d/Y',
        'datetime' => 'n/d/Y g:ia'
    ],
    'jsonrpc' => [...]
];
```

## Setup Requirements

1. **Server Requirements**
   - Apache/Nginx web server
   - PHP 7.4 or higher
   - MySQL/MariaDB database
   - XAMPP (for development)

2. **PHP Extensions**
   - PDO MySQL
   - cURL
   - JSON
   - Crypt

3. **Database**
   - MySQL/MariaDB
   - Default port: 3306
   - Database name: 'pbx'

## Directory Structure
```
/
├── functions.d/         # Function libraries
├── classes.d/           # Class definitions
├── audit_log/          # System audit logs
└── config.php          # Main configuration
```

## Configuration Details

### Database Configuration
```php
'db' => [
    'type' => 'mysql',
    'host' => 'localhost',
    'name' => 'pbx',
    'user' => 'root',
    'port' => 3306,
    'pass' => ''
]
```

### API Configuration
```php
'api' => [
    'config' => [
        'protocol' => 'http',
        'server' => 'pbx-api-config.local',
        'port' => 80,
        'version' => '1.0'
    ]
]
```

### JSONRPC Configuration
```php
'jsonrpc' => [
    'method' => 'http',
    'server' => 'jsonrpc_otsr_backend.springboard.local',
    'port' => 80,
    'path' => '/',
    'api_key' => '[your-api-key]',
    'api_pass' => '[your-api-password]'
]
```

## Authentication

The system uses API key-based authentication:
- API keys are stored in the configuration
- Each key has an associated role and description
- Passwords are securely stored and encrypted

Example:
```php
'api_keys' => [
    '[api-key]' => [
        'role' => 'role',
        'desc' => 'Description of API role',
        'pass' => '[encrypted-password]'
    ]
]
```

## Date Formats
- Date: n/d/Y (e.g., 3/15/2024)
- DateTime: n/d/Y g:ia (e.g., 3/15/2024 2:30pm)

## Security Features
- API key authentication
- Password encryption
- Audit logging
- Secure token generation

## Audit Logging
System maintains audit logs in the configured audit_log directory for:
- Configuration changes
- Authentication attempts
- System operations

## Development Setup

1. **XAMPP Configuration**
   - Install XAMPP
   - Clone repository to `C:/xampp/htdocs/pbx-webmanagement/`
   - Configure virtual hosts if needed

2. **Database Setup**
   - Create database 'pbx'
   - Configure database credentials in config.php

3. **Virtual Host Setup**
   - Configure pbx-api-config.local
   - Configure jsonrpc backend server

4. **API Configuration**
   - Set up API keys
   - Configure JSONRPC client

## API Integration

The system integrates with two main APIs:
1. PBX Configuration API
   - Manages PBX configuration
   - Version 1.0
   - HTTP-based communication

2. JSONRPC Backend
   - Handles backend operations
   - Token-based authentication
   - Secure communication

## Troubleshooting

Common issues and solutions:
1. Database Connection
   - Verify MySQL service is running
   - Check credentials in config.php
   - Confirm port availability

2. API Connection
   - Verify API server is accessible
   - Check API credentials
   - Validate virtual host configuration

3. JSONRPC Issues
   - Verify server connectivity
   - Check API token generation
   - Validate client file path

## Notes

- Development setup uses XAMPP
- Default MySQL port is 3306
- API tokens are automatically generated
- All paths should be configured according to your system setup
- Audit logs should be regularly monitored