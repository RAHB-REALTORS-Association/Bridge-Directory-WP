## [0.0.11]

### Fixed

- **Search Handler Database Access**: Corrected an issue where the search handler was not able to access the custom database table, ensuring that search functionality works as expected.

## [0.0.10]

### Fixed

- **Block Editor Script Loading**: Corrected an issue where the block editor script was not being loaded due to incorrect path resolution, ensuring the block editor functionality works as expected.

## [0.0.9]

### Fixed

- **Query Builder**: Fixed an issue where the advanced query filter was not encoding parameters correctly, leading to invalid API requests.

## [0.0.8]

### Changed

- **Class File Naming**: Renamed class files to conform to PSR-4 standards, ensuring consistency and compatibility with modern PHP development practices.

## [0.0.7]

### Added

- **GitHub Action Workflow**: Implemented a GitHub Action workflow to automate the release build process, ensuring consistent versioning and packaging of the plugin.

## [0.0.6]

### Fixed

- **ModificationTimestamp Query Parameter**: Corrected the format of the `ModificationTimestamp` parameter in API requests by appending the `.gt` operator, ensuring that only records modified since the last sync are fetched.
- **Timestamp Formatting**: Ensured that timestamps used in API requests are correctly formatted in ISO 8601 format (`YYYY-MM-DDTHH:MM:SSZ`) and in UTC time.

## [0.0.5]

### Added

- **Advanced Query Filter Setting**: Introduced a new setting on the settings page that allows administrators to add additional query parameters to API requests without affecting the `OfficeStatus` parameter. This enables more granular control over the data fetched from the API.

### Changed

- **API Client Enhancements**: Updated the API client to merge advanced query parameters while ensuring that `OfficeStatus` remains controlled by the plugin and is not overridden by the advanced filter.

### Fixed

- **Parameter Handling**: Ensured that the `OfficeStatus` parameter is always set by the plugin code during synchronization processes, maintaining consistent data retrieval of active and inactive offices as required.

---

## [0.0.4]

### Added

- **Custom Database Table**: Implemented a custom WordPress database table (`wp_bridge_directory_offices`) to store office records, improving performance and scalability when handling large datasets.

- **Database Handler Class**: Created `DB_Handler` class to manage CRUD operations on the custom database table, providing efficient data access and manipulation.

- **Activation/Deactivation Hooks**: Added hooks to create the custom database table upon plugin activation and optionally clean up upon deactivation.

### Changed

- **Data Synchronization**: Updated data synchronization processes (`Data_Sync` class) to utilize the custom database table instead of the WordPress options table for storing office data.

- **Search Functionality**: Modified the search handler to perform database queries on the custom table, enabling efficient server-side searching with support for pagination.

### Fixed

- **Performance Issues**: Resolved performance bottlenecks associated with storing large datasets in the WordPress options table by migrating to a dedicated database table.

---

## [0.0.3]

### Added

- **Enhanced Directory Display**: Redesigned the office directory to display as a grid of interactive cards, improving visual appeal and user engagement.

- **Search Bar Integration**: Implemented a search bar above the directory grid, allowing users to search for offices by name, phone number, or email.

- **Client-Side Filtering**: Added JavaScript functionality to enable instant client-side filtering of currently loaded results based on user input.

- **Debounce Functionality**: Introduced a debounce mechanism to optimize search performance by reducing the number of server requests during rapid user input.

- **Infinite Scroll**: Implemented pagination and infinite scroll, automatically loading additional offices as the user scrolls down the page.

- **AJAX Handling**: Created `AJAX_Handler` class to manage AJAX requests for loading offices dynamically without page reloads.

- **Front-End Assets**: Developed new JavaScript and CSS files (`bridge-directory.js` and `bridge-directory.css`) to support the enhanced front-end features.

### Changed

- **Block Rendering**: Updated the `Block_Register` class to render the search bar and grid layout, and to enqueue the necessary scripts and styles.

- **Search Handler**: Enhanced the `Search_Handler` class to support paginated data retrieval and to work with the AJAX requests.

### Fixed

- **Usability Issues**: Improved the user interface for better accessibility and responsiveness across different devices and screen sizes.

---

## [0.0.2]

### Added

- **Full Synchronization Process**: Implemented a manual full sync process to fetch all active office records from the Bridge Data Output API, initiated via a button on the settings page.

- **Incremental Updates**: Developed automated incremental synchronization using WordPress cron jobs, fetching only modified records since the last sync based on `ModificationTimestamp`.

- **Inactive Records Management**: Introduced functionality to fetch inactive office records and remove them from the stored data, ensuring the directory reflects current active offices.

- **Settings Page Enhancements**: Added options on the settings page to set the synchronization interval, display total cached records, and clear cached data.

- **Pagination in API Requests**: Updated the API client to fetch data in batches using pagination (`limit` and `offset` parameters), handling large datasets efficiently.

- **Selective Field Fetching**: Optimized data retrieval by requesting only necessary fields from the API, reducing data transfer and processing time.

- **Data Caching**: Implemented a caching mechanism to store office data locally, initially using the WordPress options table.

- **Scheduling**: Set up scheduling for incremental synchronization using WordPress's built-in cron system, with customizable intervals.

### Changed

- **Code Structure**: Refactored code to adhere to modern PHP development practices, including the use of namespaces, classes, and PSR-4 autoloading via Composer.

- **Error Handling**: Improved error handling and user feedback during synchronization processes.

### Fixed

- **Data Consistency**: Addressed issues related to data consistency during synchronization and caching.

---

## [0.0.1]

### Added

- **Initial Release**: Launched the initial version of the Bridge API Directory plugin with basic functionality.

- **Office Directory Display**: Provided a way to display a list of offices fetched from the Bridge Data Output API on a WordPress site.

- **Settings Page**: Created a settings page to configure the API access token and dataset name required for API requests.

- **Gutenberg Block**: Registered a custom Gutenberg block (`bridge-directory/office-list`) to allow easy insertion of the office list into posts and pages.

- **Basic Search Functionality**: Enabled simple search capability to filter offices by name, phone number, or email.

- **Caching Mechanism**: Implemented initial caching using WordPress transients to reduce API calls and improve performance.

### Fixed

- **Compatibility Issues**: Ensured compatibility with the latest version of WordPress and adherence to WordPress coding standards.

