(function ($) {
    let currentPage = 1;
    let isLoading = false;
    let noMoreResults = false;
    let lastQuery = '';
    let debounceTimeout;

    function escapeHtml(str) {
        if (str === null || str === undefined) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function isSafeUrl(url) {
        try {
            var parsed = new URL(url, window.location.origin);
            return parsed.protocol === 'http:' || parsed.protocol === 'https:';
        } catch (e) {
            return false;
        }
    }

    $(document).ready(function () {
        const $searchInput = $('#bridge-directory-search-input');
        const $cardsContainer = $('#bridge-directory-cards');
        const $loader = $('#bridge-directory-loader');

        function loadResults(reset = false) {
            if (isLoading || noMoreResults) return;
            isLoading = true;
            $loader.show();

            $.ajax({
                url: bridgeDirectory.ajax_url,
                type: 'POST',
                data: {
                    action: 'bridge_directory_load_offices',
                    nonce: bridgeDirectory.nonce,
                    page: currentPage,
                    query: lastQuery,
                },
                success: function (response) {
                    if (reset) {
                        $cardsContainer.empty();
                        currentPage = 1;
                        noMoreResults = false;
                    }
                    if (response.success) {
                        const offices = response.data.offices;
                        if (offices.length === 0) {
                            noMoreResults = true;
                        } else {
                            appendResultsToGrid(offices);
                            currentPage++;
                        }
                    }
                },
                complete: function () {
                    isLoading = false;
                    $loader.hide();
                },
            });
        }

        function isValid(value) {
            return value !== null && value !== undefined && value !== "null" && value !== "";
        }

        function appendResultsToGrid(offices) {
            offices.forEach(function (office) {
                const parts = [];

                // Handle Phone
                if (isValid(office.OfficePhoneNormalized)) {
                    parts.push(`<p><a href="tel:${escapeHtml(office.OfficePhoneNormalized)}">${escapeHtml(office.OfficePhone)}</a></p>`);
                } else if (isValid(office.OfficePhone)) {
                    parts.push(`<p>${escapeHtml(office.OfficePhone)}</p>`);
                }

                // Handle Email
                if (isValid(office.OfficeEmail) && office.OfficeEmail !== "none@onmls.ca") {
                    parts.push(`<p><a href="mailto:${escapeHtml(office.OfficeEmail)}">${escapeHtml(office.OfficeEmail)}</a></p>`);
                }

                // Handle Address
                let addressParts = [];
                if (isValid(office.OfficeAddress1)) {
                    addressParts.push(office.OfficeAddress1);
                }
                if (isValid(office.OfficeAddress2)) {
                    addressParts.push(office.OfficeAddress2);
                }
                let cityStateZip = [];
                if (isValid(office.OfficeCity)) {
                    cityStateZip.push(office.OfficeCity);
                }
                if (isValid(office.OfficeStateOrProvince)) {
                    cityStateZip.push(office.OfficeStateOrProvince);
                }
                if (isValid(office.OfficePostalCode)) {
                    cityStateZip.push(office.OfficePostalCode);
                }
                if (cityStateZip.length > 0) {
                    addressParts.push(cityStateZip.join(" "));
                }

                if (addressParts.length > 0) {
                    // Build the full address for display and for the query
                    const addressDisplay = addressParts.map(function(p) { return escapeHtml(p); }).join("<br>");
                    const addressForQuery = addressParts.join(", ");

                    // Encode the address and office name for the URL
                    const mapsUrl = `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(office.OfficeName)}+${encodeURIComponent(addressForQuery)}`;

                    // Add the clickable address to parts
                    parts.push(`<p><a href="${escapeHtml(mapsUrl)}" target="_blank">${addressDisplay}</a></p>`);
                }

                // Handle Social Media Website URL or ID
                if (isValid(office.SocialMediaWebsiteUrlOrId)) {
                    let url = office.SocialMediaWebsiteUrlOrId;
                    if (!/^https?:\/\//i.test(url)) {
                        url = `http://${url}`;
                    }
                    if (isSafeUrl(url)) {
                        const displayUrl = url.replace(/^https?:\/\//i, '');
                        parts.push(`<p><a href="${escapeHtml(url)}" target="_blank">${escapeHtml(displayUrl)}</a></p>`);
                    }
                }

                // Handle Office Name
                const officeName = isValid(office.OfficeName) ? escapeHtml(office.OfficeName) : "";
                const card = `
                    <div class="bridge-directory-card">
                        <h4>${officeName}</h4>
                        ${parts.join('')}
                    </div>
                `;

                $cardsContainer.append(card);
            });
        }

        function debounceSearch() {
            clearTimeout(debounceTimeout);
            debounceTimeout = setTimeout(function () {
                lastQuery = $searchInput.val();
                currentPage = 1;
                noMoreResults = false;
                loadResults(true);
            }, 300);
        }

        $searchInput.on('input', debounceSearch);

        $(window).on('scroll', function () {
            if ($(window).scrollTop() + $(window).height() >= $(document).height() - 100) {
                loadResults();
            }
        });

        // Initial load
        loadResults();
    });
})(jQuery);
