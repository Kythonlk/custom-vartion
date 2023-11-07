console.log('working well');

jQuery(document).ready(function($) {
    // Function to fetch colors based on the selected brand using AJAX
    function fetchColors(selectedBrandId) {
        var $colorSelect = $('#color');
        $colorSelect.empty().append('<option value="">Loading...</option>');

        if (selectedBrandId) {
            $.ajax({
                type: 'GET',
                url: '/wp-json/custom-api/v1/colors/' + selectedBrandId, // Adjust the URL to your custom API endpoint
                success: function(data) {
                    $colorSelect.empty().append('<option value="">Select Color</option>');
                    $.each(data, function(index, color) {
                        $colorSelect.append($('<option>', {
                            value: color.color_code,
                            text: color.color_code
                        }));
                    });
                }
            });
        } else {
            $colorSelect.empty().append('<option value="">Select Brand First</option>');
        }
    }

    // Event listener for brand selection change within the modal
    $('#brand').on('change', function() {
        var selectedBrandId = $(this).val();
        fetchColors(selectedBrandId);
    });

    // Event listener for opening the modal
    $('#selectionModal').on('show.bs.modal', function() {
        // Reset the color selector when the modal is opened
        $('#color').val('');
    });
});
