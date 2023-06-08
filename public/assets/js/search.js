$(document).ready(function () {
    $('#searchInput').click(function () {
        $('#searchForm').submit(); // Soumettre le formulaire
    });

    $('#searchForm').submit(function (event) {
        event.preventDefault();

        var searchQuery = $('#search').val();
        var lowercaseSearchQuery = searchQuery.toLowerCase().trim();
        var formattedSearchQuery = lowercaseSearchQuery.replace(/ /g, '-');
        var encodedSearchQuery = encodeURIComponent(formattedSearchQuery);
        var redirectUrl = $('#searchInput').data('url') + encodedSearchQuery;

        window.location.href = redirectUrl;
    });
});
