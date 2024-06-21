document.addEventListener("DOMContentLoaded", function() 
{
    const configElement = document.getElementById('js-config');
    const apiUrl = configElement.getAttribute('data-api-url');
    const refreshUrl = configElement.getAttribute('data-refresh-url');

    const reservationForm = document.getElementById("reservationForm");
    reservationForm.addEventListener("submit", function(event) 
	{
        event.preventDefault();  // Empêche la soumission normale du formulaire
        const formData = new FormData(reservationForm);
        
        fetch(apiUrl, 
		{
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.querySelectorAll('input[name="chaise[]"]:checked').forEach(input => 
				{
                    input.disabled = true;  // Désactiver les chaises cochées
                });
            } 
			else 
			{
                alert('Erreur lors de la réservation');
            }
        });
    });

    const reactualiserButton = document.getElementById("reinitialiserChaises");
    reactualiserButton.addEventListener("click", function() 
	{
        fetch(refreshUrl)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.querySelectorAll('input[name="chaise[]"]').forEach(input => 
				{
                    input.disabled = false;  // Réactiver toutes les chaises
                });
            }
        });
    });
});