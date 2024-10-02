# Bridge API Directory

Displays a searchable directory of offices using the Bridge Interactive API.

## Installation

1. Upload the plugin files to the `/wp-content/plugins/bridge-directory` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Install dependencies:
   - Run `composer install` to set up autoloading.
   - Run `npm install` and `npm run build` to build block editor scripts.

## Usage

1. Navigate to `Settings -> Bridge Directory` to configure the plugin:
   - **Access Token:** Obtain this from your Bridge Data Output API account.
   - **Dataset Name:** Specify the dataset to query (e.g., `itso`).
   - **Sync Interval:** Set how often (in hours) to perform incremental syncs.
   - **Advanced Query Filter:** Customize additional query parameters for the API requests. These parameters will be added to the API calls but will not override `OfficeStatus`, which is managed by the plugin. Do not include `OfficeStatus` in this field. Separate multiple parameters with `&`.
2. Click the **Full Sync** button to initiate the initial data synchronization.
3. In the WordPress block editor, add the **Bridge Office List** block to your page or post.
4. Customize the block settings in the editor sidebar.

## Features

- **Full Sync:** Manually initiate a full synchronization of all active offices.
- **Incremental Sync:** Automatically fetch updates and remove inactive offices at set intervals.
- **Search Functionality:** Users can search offices by name, phone, or email.
- **Customizable Display:** Adjust the number of columns and rows in the block editor.
- **Cache Management:** Clear the cache manually if needed.
- **Enhanced Directory Display:** Offices are displayed as a grid of cards for better visual presentation.
- **Live Search with Debounce:** Users can search offices with instant feedback, and the search input is debounced to optimize performance.
- **Infinite Scroll:** As users scroll down, more offices are loaded automatically without needing to click pagination links.

## Data Storage

- The plugin uses a custom database table (`wp_bridge_directory_offices`) to store office records for better performance with large datasets.

## Changelog

- **Version 0.0.1**: Established the foundation with basic directory display and search capabilities.
- **Version 0.0.2**: Introduced robust data synchronization processes to handle large datasets efficiently.
- **Version 0.0.3**: Enhanced the front-end user interface with a grid display, search bar, and infinite scroll.
- **Version 0.0.4**: Improved performance and scalability by implementing a custom database table for data storage.
- **Version 0.0.5**: Added flexibility with an advanced query filter setting, allowing administrators to customize API requests without compromising core functionality.
