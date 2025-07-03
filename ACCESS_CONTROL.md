# Access Control for Simple Photo Feed

## Overview

Starting with version 1.4.3, Simple Photo Feed includes configurable access control that allows site administrators to choose which user roles can access and configure the plugin.

## Default Behavior

By default, the plugin remains secure and only allows administrators (`manage_options` capability) to access the plugin settings. This ensures that sensitive Instagram API credentials and feed configuration remain protected.

## Configurable Options

Site administrators can now choose from three access levels:

### 1. Administrators Only (Default)
- **Capability**: `manage_options`
- **User Roles**: Administrators only
- **Use Case**: Maximum security, recommended for most sites

### 2. Editors and Above
- **Capability**: `edit_posts`
- **User Roles**: Administrators, Editors
- **Use Case**: Allow content editors to manage Instagram feeds

### 3. Authors and Above
- **Capability**: `publish_posts`
- **User Roles**: Administrators, Editors, Authors
- **Use Case**: Allow content creators to manage their own Instagram feeds

## How to Configure

1. Go to **WordPress Admin → Simple Photo Feed**
2. Scroll down to the **Access Control** section
3. Select your preferred access level from the dropdown
4. Click **Save Options**

## Security Considerations

- The plugin validates that only allowed capabilities are used
- Invalid capabilities automatically fall back to `manage_options`
- All AJAX actions respect the configured access level
- The setting is stored securely in WordPress options
- **Access Control setting is only visible to administrators** - Users granted access through the setting cannot modify the access control itself
- **Server-side validation** - Even if someone tries to submit the access control setting programmatically, only administrators can actually save it

## Developer Customization

Developers can use the `spf_required_capability` filter to customize access control programmatically:

```php
add_filter( 'spf_required_capability', function( $capability ) {
    // Allow custom capability
    return 'custom_capability';
});
```

## Migration from Previous Versions

- Sites upgrading from version 1.4.3 or earlier will automatically use the default "Administrators Only" setting
- No manual configuration is required
- Existing security is maintained

## Best Practices

1. **Start with Administrators Only**: Use the most restrictive setting unless you have a specific need for broader access
2. **Consider Your Team**: Choose the access level based on who needs to manage Instagram feeds
3. **Regular Review**: Periodically review who has access to ensure security
4. **Documentation**: Inform your team about the access control settings

## Troubleshooting

If users report they cannot access the plugin settings:

1. Check the **Access Control** setting in the plugin configuration
2. Verify the user has the required WordPress role
3. Ensure the user's role hasn't been modified by other plugins
4. Check for any custom capability filters that might be interfering
