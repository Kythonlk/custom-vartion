console.log('working well');

jQuery(document).ready(function($) {
    function fetchColors(selectedBrandId) {
        var $colorSelect = $('#color');
        $colorSelect.empty().append('<option value="">Loading...</option>');

        if (selectedBrandId) {
            $.ajax({
                type: 'GET',
                url: '/wp-json/custom-api/v1/colors/' + selectedBrandId,
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

    $('#brand').on('change', function() {
        var selectedBrandId = $(this).val();
        fetchColors(selectedBrandId);
    });
    
    $('#selectionModal').on('show.bs.modal', function() {
        $('#color').val('');
    });
});

