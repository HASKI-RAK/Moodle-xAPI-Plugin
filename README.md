# Moodle Logstore xAPI Plugin

[![Moodle Plugin](https://img.shields.io/badge/Moodle-Plugin-orange.svg)](https://moodle.org/plugins/view/logstore_xapi)
[![License: GPL v3](https://img.shields.io/badge/License-GPLv3-blue.svg)](https://www.gnu.org/licenses/gpl-3.0)

> A powerful Moodle plugin that emits [xAPI](https://github.com/adlnet/xAPI-Spec/blob/master/xAPI.md) (Experience API) statements using the [Moodle](https://moodle.org/) Logstore framework, enabling comprehensive learning analytics and interoperability with Learning Record Stores (LRS).

## 📋 Table of Contents

- [Overview](#overview)
- [Key Features](#key-features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
- [Architecture](#architecture)
- [Development](#development)
- [Testing](#testing)
- [Documentation](#documentation)
- [Contributing](#contributing)
- [Credits](#credits)
- [License](#license)

## 🎯 Overview

The Moodle Logstore xAPI Plugin transforms Moodle events into standardized xAPI statements and sends them to a Learning Record Store (LRS). This enables:

- **Learning Analytics**: Track and analyze learner interactions across your Moodle platform
- **Interoperability**: Share learning data with other xAPI-compliant systems
- **Reporting**: Generate comprehensive reports using your LRS of choice
- **Compliance**: Follow international standards for learning experience data

This plugin captures a wide range of Moodle activities including course completions, module interactions, assessments, and more, converting them into xAPI statements that provide detailed insights into the learning process.

## ✨ Key Features

- **Comprehensive Event Coverage**: Supports numerous Moodle events across core and activity modules
- **Background Processing**: Runs in background mode via cron to avoid blocking page responses
- **Batch Processing**: Efficiently processes events in configurable batches
- **Error Handling**: Robust error logging and retry mechanisms for failed events
- **Historical Events**: Process past events from your Moodle logs
- **Flexible Configuration**: Customize LRS endpoints, authentication, and processing parameters
- **Standard Compliance**: Generates valid xAPI 1.0.3 statements
- **Extensible**: Easy to add support for new events and custom statements

### Supported Moodle Modules

The plugin supports events from numerous Moodle modules including:
- Core (course completion, user enrollment, etc.)
- Assignments
- BigBlueButton
- Books
- Chat
- Choice
- Database
- Feedback
- Forum
- Glossary
- H5P
- Page
- Quiz
- SCORM
- Survey
- Wiki
- Workshop
- And many more...

## 📦 Requirements

- **Moodle**: Version 3.9 or later (tested up to 4.0.x)
- **PHP**: Version 7.2 or later
- **PHP Extensions**: json
- **Composer**: For dependency management
- **Learning Record Store**: Any xAPI-compliant LRS

### Compatibility

| Moodle Version | Plugin Version | Status |
|----------------|----------------|--------|
| 3.9+           | 2022101804     | ✅ Stable |
| 4.0.x          | 2022101804     | ✅ Supported |

## 🚀 Installation

The plugin can be installed in three ways:

### Method 1: Install via Git (Recommended for Development)

If you want to contribute to the plugin or need the latest development version:

1. Navigate to your Moodle installation directory:
   ```bash
   cd /path/to/moodle/admin/tool/log/store
   ```

2. Clone the repository:
   ```bash
   git clone https://github.com/HASKI-RAK/Moodle-xAPI-Plugin.git xapi
   cd xapi
   ```

3. Install dependencies:
   ```bash
   php -r "readfile('https://getcomposer.org/installer');" | php
   rm -rf vendor
   php composer.phar install --prefer-source
   ```

4. Navigate to your Moodle site as an administrator to complete the installation

📖 [Detailed Git Installation Guide](docs/install-with-git.md)

### Method 2: Install via ZIP File

For production deployments:

1. Download the latest release ZIP file from the [releases page](https://github.com/HASKI-RAK/Moodle-xAPI-Plugin/releases)
2. Extract and prepare dependencies:
   ```bash
   unzip moodle-logstore_xapi.zip
   cd moodle-logstore_xapi
   php -r "readfile('https://getcomposer.org/installer');" | php
   php composer.phar install --no-dev
   ```
3. Navigate to: `Site administration → Plugins → Install plugins`
4. Upload the ZIP file and follow the installation wizard

📖 [Detailed ZIP Installation Guide](docs/install-with-zip.md)

### Method 3: Direct Download

1. Download the plugin from the [Moodle plugins directory](https://moodle.org/plugins/view/logstore_xapi)
2. Extract to `moodle/admin/tool/log/store/xapi`
3. Complete the installation through the Moodle interface

📖 [Detailed Download Installation Guide](docs/install-with-download.md)

## ⚙️ Configuration

After installation, configure the plugin to connect to your LRS:

### Basic Configuration

1. Navigate to `Site administration → Plugins → Logging → Logstore xAPI`

2. Configure the following settings:

   **LRS Connection:**
   - **Endpoint**: Your LRS endpoint URL (e.g., `http://your.lrs/xAPI`)
   - **Username**: Your LRS basic auth key/username
   - **Password**: Your LRS basic auth secret/password
   - **API Key Auth**: Enable if using API key authentication
   - **Auth**: API key value (if applicable)

   **Processing Options:**
   - **Background Mode**: Enable to process events via cron (recommended, enabled by default)
   - **Max Batch Size**: Number of events to process per batch (default: 30)

3. Click "Save changes"

### Enable the Plugin

1. Navigate to `Site administration → Plugins → Logging → Manage log stores`
2. Enable the "Logstore xAPI" plugin

📖 [Detailed Configuration Guide](docs/configure-the-plugin.md) | [Enable Plugin Guide](docs/enable-the-plugin.md)

## 🔧 Usage

### Real-time Event Processing

Once enabled, the plugin automatically:
1. Captures Moodle events as they occur
2. Queues them for processing (in background mode)
3. Transforms events into xAPI statements via scheduled cron tasks
4. Sends statements to your configured LRS

### Historical Events

To process events that occurred before the plugin was installed:

```sql
-- Move events from standard log to xAPI log table
INSERT INTO mdl_logstore_xapi_log
SELECT * FROM mdl_logstore_standard_log
WHERE timecreated BETWEEN [start_timestamp] AND [end_timestamp]
```

The plugin will process these events during the next cron run.

📖 [Historical Events Guide](docs/historical-events.md)

### Monitoring

Monitor plugin activity through:
- **Error Logs**: Check `classes/log/error_log.txt` for transformation or transmission errors
- **Reports**: Navigate to the plugin's report page to view event processing status
- **LRS Dashboard**: View statements in your LRS interface

## 🏗️ Architecture

### Component Overview

```
moodle-logstore_xapi/
├── src/
│   ├── transformer/        # Event-to-xAPI transformation logic
│   │   ├── events/        # Event handlers by module
│   │   ├── utils/         # Utility functions
│   │   └── handler.php    # Main transformer handler
│   └── loader/            # LRS communication layer
│       ├── utils/         # Loading utilities
│       └── handler.php    # Main loader handler
├── classes/
│   ├── log/              # Log store implementation
│   ├── task/             # Scheduled tasks
│   ├── form/             # Admin forms
│   └── privacy/          # GDPR compliance
├── tests/                # PHPUnit tests
├── docs/                 # Documentation
└── lang/                 # Language strings
```

### Data Flow

1. **Event Capture**: Moodle events are captured by the logstore
2. **Queueing**: Events are stored in `mdl_logstore_xapi_log` table
3. **Transformation**: Cron task transforms events to xAPI statements
4. **Loading**: Statements are sent to the LRS in batches
5. **Cleanup**: Successfully processed events are removed from the queue

### Code Structure

The plugin follows a layered architecture:

- **Data Layer** (Repository): Reads/writes data from database, LRS, or external APIs
- **Business Layer** (Service): Transforms and processes data
- **Presentation Layer**: Admin interfaces and reports

## 👨‍💻 Development

### Setting Up Development Environment

1. Fork and clone the repository
2. Install dependencies:
   ```bash
   composer install
   ```
3. Create a new branch for your feature:
   ```bash
   git checkout -b feature/your-feature-name
   ```

### Adding Support for New Events

To add support for a new Moodle event:

1. **Create Transformer Function**: Add a new file in `src/transformer/events/[module]/[event_name].php`

2. **Map the Event**: Update `src/transformer/get_event_function_map.php` to map the event to your transformer

3. **Create Tests**: Add test cases in `tests/[module]/[event_name]/` with:
   - `test.php` - Test runner
   - `data.json` - Mocked database data
   - `event.json` - Mocked event data
   - `statements.json` - Expected output

4. **Run Tests**: Verify your implementation:
   ```bash
   ./vendor/bin/phpunit
   ```

📖 [New Events Guide](docs/new-events.md) | [New Statements Guide](docs/new-statements.md)

### Code Standards

- Follow [Moodle coding standards](https://docs.moodle.org/dev/Coding_style)
- Write comprehensive tests for new features
- Document public functions and complex logic
- Ensure backward compatibility

## 🧪 Testing

### Running Tests

Execute all tests:
```bash
./vendor/bin/phpunit
```

Run specific test suite:
```bash
./vendor/bin/phpunit tests/mod_assign/
```

### Test Structure

Each test consists of four files:
- `test.php` - Executes the test
- `data.json` - Mocks Moodle database
- `event.json` - Mocks logstore event
- `statements.json` - Expected xAPI statements

📖 [Testing Guide](docs/testing.md)

### Continuous Integration

The plugin uses GitHub Actions for automated testing across multiple Moodle and PHP versions. See `.github/workflows/moodle-plugin-ci.yml` for details.

## 📚 Documentation

Comprehensive documentation is available in the `docs/` directory:

### Installation Guides
- [Install with Git](docs/install-with-git.md)
- [Install with ZIP](docs/install-with-zip.md)
- [Install with Download](docs/install-with-download.md)

### Configuration & Usage
- [Configure the Plugin](docs/configure-the-plugin.md)
- [Enable the Plugin](docs/enable-the-plugin.md)
- [Historical Events](docs/historical-events.md)

### Development
- [Supporting New Events](docs/new-events.md)
- [Creating New Statements](docs/new-statements.md)
- [Changing Statements](docs/change-statements.md)
- [Testing](docs/testing.md)

## 🤝 Contributing

We welcome contributions! Here's how you can help:

1. **Fork the Repository**: Create your own fork on GitHub
2. **Create a Branch**: Make your changes in a feature branch
3. **Follow Guidelines**: Adhere to coding standards and include tests
4. **Submit a Pull Request**: Use our [PR template](.github/PULL_REQUEST_TEMPLATE.md)
5. **Code Review**: Respond to feedback from maintainers

### Reporting Issues

Found a bug or have a feature request? Please use our [issue tracker](https://github.com/HASKI-RAK/Moodle-xAPI-Plugin/issues) and follow the [issue template](.github/ISSUE_TEMPLATE.md).

📖 [Contributing Guidelines](.github/CONTRIBUTING.md)

## 👏 Credits

### Original Plugin
- **Original Repository**: [xAPI-vle/moodle-logstore_xapi](https://github.com/xAPI-vle/moodle-logstore_xapi)
- **Full-Event Extension**: [PR #841](https://github.com/xAPI-vle/moodle-logstore_xapi/pull/841)

### Maintainers
- Jerret Fowler - jerrett.fowler@gmail.com
- [Ryan Smith](https://www.linkedin.com/in/ryan-smith-uk/)
- David Pesce - david.pesce@exputo.com

### HASKI Fork
This fork is maintained by the HASKI-RAK team with additional features and enhancements.

## 📄 License

This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the [GNU General Public License](LICENSE) for more details.

## 🔗 Links

- [Moodle Plugins Directory](https://moodle.org/plugins/view/logstore_xapi)
- [xAPI Specification](https://github.com/adlnet/xAPI-Spec/blob/master/xAPI.md)
- [Moodle Documentation](https://docs.moodle.org/)
- [Composer Documentation](https://getcomposer.org/doc/)

## 🆘 Support

- **Documentation**: Check the [docs](docs/) directory
- **Issues**: [GitHub Issue Tracker](https://github.com/HASKI-RAK/Moodle-xAPI-Plugin/issues)
- **Community**: Join discussions in the issue tracker

---

**Note**: Before creating a Moodle Plugin ZIP for distribution, ensure dependencies are installed:
```bash
composer install --no-dev
```

For development:
```bash
composer install --prefer-source
```
