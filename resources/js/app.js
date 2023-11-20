import './bootstrap';

function sendInvitation() {
    var formData = $('#invitationForm').serialize();

    $.ajax({
        url: '/send-invitation', // TODO
        type: 'POST',
        data: formData,
        success: function (response) {
            // Handle success (e.g., show a success message)
            console.log(response.message);
        },
        error: function (error) {
            // Handle error (e.g., show an error message)
            console.error(error.responseJSON.error);
        }
    });
}