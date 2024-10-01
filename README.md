# Bridge API Directory

Displays a searchable directory of offices using the Bridge Data Output API.

## Installation

1. Upload the plugin files to the `/wp-content/plugins/bridge-directory` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Use Composer to install dependencies: `composer install`.
4. Build the block editor script using Webpack: `npm install` and `npm run build`.

## Usage

1. Navigate to `Settings -> Bridge Directory` to set your API Access Token, Dataset Name, and Cache Lifetime.
2. In the WordPress block editor, add the **Bridge Office List** block to your page or post.
3. Customize the block settings in the editor sidebar.

## Configuration

- **Access Token:** Obtain this from your Bridge Data Output API account.
- **Dataset Name:** Specify the dataset to query (e.g., `itso`).
- **Cache Lifetime:** Set how long (in hours) the data should be cached.

## Development

- Follow PSR-4 autoloading standards.
- Use Composer for dependency management.
- Use Webpack to bundle JavaScript for the block editor.

## Testing

- Run unit tests using PHPUnit.
- Ensure WordPress coding standards are met.

