(function ($) {
	'use strict';

	$(function () {
		var $modal = $('#petAvatarModal');
		if (!$modal.length) {
			return;
		}

		var maxBytes = 8 * 1024 * 1024;
		var $source = $('#petAvatarSource');
		var $editor = $('#petAvatarEditor');
		var $cropper = $('#petAvatarCropper');
		var $crop = $('#petAvatarCrop');
		var $save = $('#petAvatarSave');
		var $message = $('#petAvatarClientMessage');
		var cropReady = false;

		function showMessage(message) {
			$message.text(message).removeClass('d-none');
		}

		function clearMessage() {
			$message.text('').addClass('d-none');
		}

		function destroyCropper() {
			if (cropReady) {
				$cropper.croppie('destroy');
				cropReady = false;
			}
			$cropper.empty();
		}

		function resetEditor() {
			destroyCropper();
			$source.val('');
			$crop.val('');
			$editor.addClass('d-none');
			$save.prop('disabled', true);
			clearMessage();
		}

		$source.on('change', function () {
			var file = this.files && this.files[0];
			destroyCropper();
			$crop.val('');
			$editor.addClass('d-none');
			$save.prop('disabled', true);
			clearMessage();

			if (!file) {
				return;
			}
			if (file.size > maxBytes) {
				showMessage($modal.data('too-large'));
				return;
			}
			if (file.type && $.inArray(file.type, ['image/jpeg', 'image/png']) === -1) {
				showMessage($modal.data('invalid-type'));
				return;
			}

			var reader = new FileReader();
			reader.onerror = function () {
				showMessage($modal.data('invalid-image'));
			};
			reader.onload = function (event) {
				clearMessage();
				$editor.removeClass('d-none');
				var boundarySize = Math.min(300, Math.max(220, Math.floor($editor.width())));
				var viewportSize = Math.min(250, boundarySize - 40);
				$cropper.croppie({
					viewport: { width: viewportSize, height: viewportSize, type: 'square' },
					boundary: { width: boundarySize, height: boundarySize },
					enableOrientation: true,
					enableExif: true
				});
				cropReady = true;
				$cropper.croppie('bind', { url: event.target.result }).then(function () {
					$save.prop('disabled', false);
				}).catch(function () {
					resetEditor();
					showMessage($modal.data('invalid-image'));
				});
			};
			reader.readAsDataURL(file);
		});

		$('.pet-avatar-rotate').on('click', function () {
			if (cropReady) {
				$cropper.croppie('rotate', parseInt($(this).data('deg'), 10));
			}
		});

		$save.on('click', function () {
			if (!cropReady || !$source[0].files.length) {
				showMessage($modal.data('invalid-image'));
				return;
			}

			$save.prop('disabled', true);
			$cropper.croppie('result', {
				type: 'base64',
				format: 'jpeg',
				quality: 0.9,
				size: { width: 512, height: 512 }
			}).then(function (result) {
				$crop.val(result);
				document.getElementById('petAvatarUploadForm').submit();
			}).catch(function () {
				$save.prop('disabled', false);
				showMessage($modal.data('invalid-image'));
			});
		});

		$modal.on('hidden.bs.modal', resetEditor);
	});
})(jQuery);
