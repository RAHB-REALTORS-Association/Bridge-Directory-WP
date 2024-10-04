(function ($) {
    let currentPage = 1;
    let isLoading = false;
    let noMoreResults = false;
    let lastQuery = '';
    let debounceTimeout;

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
                    parts.push(`<p><a href="tel:${office.OfficePhoneNormalized}">${office.OfficePhone}</a></p>`);
                } else if (isValid(office.OfficePhone)) {
                    parts.push(`<p>${office.OfficePhone}</p>`);
                }

                // Handle Email
                if (isValid(office.OfficeEmail) && office.OfficeEmail !== "none@onmls.ca") {
                    parts.push(`<p><a href="mailto:${office.OfficeEmail}">${office.OfficeEmail}</a></p>`);
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
                    parts.push(`<p>${addressParts.join("<br>")}</p>`);
                }

                // Handle Social Media Website URL or ID
                if (isValid(office.SocialMediaWebsiteUrlOrId)) {
                    let url = office.SocialMediaWebsiteUrlOrId;
                    if (!/^https?:\/\//i.test(url)) {
                        url = `http://${url}`;
                    }
                    const displayUrl = url.replace(/^https?:\/\//i, '');
                    parts.push(`<p><a href="${url}" target="_blank">${displayUrl}</a></p>`);
                }

                // Handle Office Name
                const officeName = isValid(office.OfficeName) ? office.OfficeName : "";
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
