# CraftMini Framework Testing

This document describes the comprehensive testing setup for the CraftMini Framework, including GitHub Actions workflows and local testing scripts.

## 🚀 GitHub Actions Workflow

### File: `.github/workflows/test-framework.yml`

This workflow automatically tests the framework on:

**Operating Systems:**
- ✅ Ubuntu Latest (Linux)
- ✅ Windows Latest  
- ✅ macOS Latest

**PHP Versions:**
- ✅ PHP 7.1 (minimum supported)
- ✅ PHP 8.4 (latest stable)

**Test Coverage:**
- ✅ Framework startup and initialization
- ✅ Default route (`/`) accessibility and content
- ✅ API hello routes functionality (`/api/hello/{name}`)
- ✅ Error handling (404 responses)
- ✅ Composer autoloading
- ✅ Core framework components
- ✅ Hash functionality (bcrypt, argon2i)
- ✅ Session and flash message handling

## 🧪 Local Testing

### 1. Basic Framework Test
```bash
php test-framework.php
```

Tests:
- PHP version compatibility (≥7.1)
- Required extensions (json, mysqli, pdo, pdo_sqlite)
- Composer autoloader
- Core classes availability
- File structure integrity
- Framework initialization

### 2. Manual Testing

Start the development server:
```bash
php -S localhost:8000 -t public/
```

Test these URLs:
- `http://localhost:8000/` - Default route
- `http://localhost:8000/api/hello/test` - Hello API with 'test'
- `http://localhost:8000/nonexistent` - 404 error handling

## 📋 Expected Behavior

### Default Route (`/`)
- Shows welcome message with framework version

### API Hello Routes
- `GET /api/hello/{name}` - Returns "Hello, {name}" message
- Tested with different names: 'test', 'world', 'Vietnam'
- Supports proper HTTP methods (GET, POST, PUT, DELETE)

### Error Handling
- Non-existent routes return 404 status
- Proper error logging to `public/logs/`

## 🔧 Configuration

### Environment Variables
Create `.env` file:
```
APP_ENVIRONMENT=testing
APP_DEBUG=true
```


### Dependencies
- PHP ≥7.1
- Composer
- Required extensions: json, mysqli, pdo, pdo_sqlite

## 🎯 Testing Strategy

1. **Unit Tests**: Individual component testing
2. **Integration Tests**: Framework startup and routing
3. **End-to-End Tests**: Full HTTP request/response cycle
4. **Cross-Platform Tests**: Multiple OS and PHP versions
5. **Error Handling Tests**: 404 responses and error logging

## 🚨 Troubleshooting

### Common Issues

1. **Composer not found**: Install Composer globally
2. **PHP extensions missing**: Install required PHP extensions
3. **Database permission errors**: Check file permissions on database files
4. **Port 8000 in use**: Use different port or kill existing process

### Debug Mode

Enable debug mode in `.env`:
```
APP_DEBUG=true
```

Check logs in `public/logs/` for detailed error information.

## 📊 Test Results

The GitHub Actions workflow will show:
- ✅ Green checkmarks for passed tests
- ❌ Red X marks for failed tests
- Detailed logs for debugging failures
- Test artifacts uploaded on failure

Each test matrix combination (OS × PHP version) runs independently, so you can see exactly which combinations pass or fail.
