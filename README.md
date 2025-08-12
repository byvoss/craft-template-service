# Template Service Plugin for Craft CMS 5

Enhanced template services for Craft CMS 5 - Brings back the template autocomplete from Craft 3 and adds more developer tools.

## Features

### ✅ Template Autocomplete (v1.0)
- Full nested folder support
- Works in all template fields (Sections, Entry Types, Categories, etc.)
- Keyboard navigation (Arrow keys, Enter, Escape)
- Smart filtering while typing
- Distinguishes between folders 📁 and templates 📄
- Respects hidden folders (prefixed with `_`)

### 🚀 Coming Soon
- Template Preview
- Variable Inspector
- Performance Profiler
- Snippet Library
- Template Generator

## Requirements

- Craft CMS 5.0.0 or later
- PHP 8.2 or later

## Installation

### Via Composer

```bash
composer require byvoss/template-service
```

### Manual Installation

1. Download the plugin
2. Place in `plugins/template-service` folder
3. Run `composer dump-autoload`
4. Install via Control Panel or CLI

```bash
php craft plugin/install template-service
```

## Usage

Once installed, the plugin automatically enhances all template input fields in the Control Panel:

1. **Focus** on any template field
2. **Start typing** to filter templates
3. **Use arrow keys** to navigate
4. **Press Enter** to select
5. **Press Escape** to close

### Supported Locations

- Section Settings → Entry Template
- Entry Type Settings → Template
- Category Group Settings → Template
- Global Set Settings → Template
- Any custom field with "template" in the name

## Configuration

No configuration needed! The plugin works out of the box.

## Troubleshooting

### Templates not showing up

1. Check that templates exist in `templates/` folder
2. Verify file permissions
3. Clear Craft's cache: `php craft clear-caches/all`

### Autocomplete not appearing

1. Hard refresh the browser (Cmd+Shift+R / Ctrl+Shift+F5)
2. Check browser console for errors
3. Ensure JavaScript is enabled

## Support

- **Issues**: [GitHub Issues](https://github.com/byvoss/craft-template-service/issues)
- **Documentation**: [GitHub Wiki](https://github.com/byvoss/craft-template-service/wiki)
- **Email**: support@byvoss.tech

## Roadmap

### Version 1.1
- Template Preview on hover
- Template variables inspector
- Quick template creation

### Version 1.2
- Performance profiler
- Template dependencies graph
- Unused template detection

### Version 2.0
- AI-powered template suggestions
- Template snippet library
- Visual template builder

## License

This plugin is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Credits

Developed by [ByVoss Technologies](https://byvoss.tech)

Special thanks to the Craft CMS community for feedback and suggestions.

---

**Love this plugin?** Consider [sponsoring](https://github.com/sponsors/byvoss) the development or giving it a ⭐ on GitHub!