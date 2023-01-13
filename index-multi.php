<!DOCTYPE html>
<html>
<head>
	<title>Whatsapp API</title>
	<style>
	.client {
		border: 1px solid #ccc;
		padding: 20px;
		box-sizing: border-box;
		display: inline-block;
		margin: 10px;
	}
	.hide {
		display: none;
	}
	</style>
</head>
<body>

	<div id="app">
		<h1>Whatsapp API</h1>
		<p>Fantecno</p>
		<div class="form-container">
			<label for="client-id">ID</label><br>
			<input type="text" id="client-id" placeholder="Masukkan ID">
			<br><br>
			<label for="client-description">Deskripsi</label><br>
			<textarea rows="3" id="client-description" placeholder="Masukkan deskripsi"></textarea>
			<br><br>
			<button class="add-client-btn">Tambah Client</button>
		</div>
		<hr>
		<div class="client-container">
			<div class="client hide">
				<h3 class="title"></h3>
				<p class="description"></p>
				<img src="" alt="QR Code" id="qrcode">
				<h3>Logs:</h3>
				<ul class="logs"></ul>
			</div>
		</div>
	</div>

	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/socket.io/2.3.0/socket.io.js"></script>
	<script>
		$(document).ready(function() {
			var socket = io();

			// Ketika button tambah diklik
			$('.add-client-btn').click(function() {
				var clientId = $('#client-id').val();
				var clientDescription = $('#client-description').val();

        /**
         * Some peoples want to use the phone number as the ID
         * But because we use the ID as the html class attribute value: class="<The ID>"
         * It won't work. Read more on https://www.w3.org/TR/REC-html40/types.html#type-cdata
         * 
         * So, here we add the prefix to solve that problem
         * Each ID will automatically added a 'client-' prefix for the class attribute
         */
        var clientClass = 'client-' + clientId;
				var template = $('.client').first().clone()
										   .removeClass('hide')
										   .addClass(clientClass);

				template.find('.title').html(clientId);
				template.find('.description').html(clientDescription);
				$('.client-container').append(template);

				socket.emit('create-session', {
					id: clientId,
					description: clientDescription
				});
			});

			socket.on('init', function(data) {
				$('.client-container .client').not(':first').remove();
				console.log(data);
				for (var i = 0; i < data.length; i++) {
					var session = data[i];

					var clientId = session.id;
					var clientDescription = session.description;

          var clientClass = 'client-' + clientId;
					var template = $('.client').first().clone()
											   .removeClass('hide')
											   .addClass(clientClass);

					template.find('.title').html(clientId);
					template.find('.description').html(clientDescription);
					$('.client-container').append(template);

					if (session.ready) {
						$(`.client.${clientClass} .logs`).append($('<li>').text('Whatsapp is ready!'));
					} else {
						$(`.client.${clientClass} .logs`).append($('<li>').text('Connecting...'));
					}
				}
			});

			socket.on('remove-session', function(id) {
				$(`.client.client-${id}`).remove();
			});

			socket.on('message', function(data) {
				$(`.client.client-${data.id} .logs`).append($('<li>').text(data.text));
			});

			socket.on('qr', function(data) {
				$(`.client.client-${data.id} #qrcode`).attr('src', data.src);
				$(`.client.client-${data.id} #qrcode`).show();
			});

			socket.on('ready', function(data) {
				$(`.client.client-${data.id} #qrcode`).hide();
			});

			socket.on('authenticated', function(data) {
				$(`.client.client-${data.id} #qrcode`).hide();
			});
		});
	</script>
</body>
</html>
