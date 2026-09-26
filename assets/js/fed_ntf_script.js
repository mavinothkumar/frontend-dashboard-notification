/**
 * Frontend Dashboard Notification JS Scripts
 */

(function($) {
	'use strict';

	$(document).ready(function() {

		// -------------------------------------------------------------
		// 1. Frontend: Close / Dismiss Notification
		// -------------------------------------------------------------
		$(document).on('click', '.fed_notification_close_button', function(e) {
			e.preventDefault();
			var $btn = $(this);
			var $container = $btn.closest('.fed_notification_container');
			var closeUrl = $container.data('close-url');
			var dismissType = $container.data('dismiss');

			// Add hiding class for smooth transition
			$container.addClass('fed-ntf-hiding');

			setTimeout(function() {
				$container.slideUp(200, function() {
					$(this).remove();
				});
			}, 200);

			// If permanent dismissal, fire AJAX request
			if (dismissType === 'permanent' && closeUrl && closeUrl.length > 5) {
				$.ajax({
					type: 'POST',
					url: closeUrl,
					dataType: 'json',
					success: function(response) {
						// Dismiss recorded
					},
					error: function() {
						// Fail gracefully
					}
				});
			}
		});

		// -------------------------------------------------------------
		// 2. Admin: Quick Status Toggle
		// -------------------------------------------------------------
		$(document).on('click', '.fed-ntf-toggle-btn', function(e) {
			e.preventDefault();
			var $btn = $(this);
			var ntfId = $btn.data('id');
			var nonce = $btn.data('nonce');

			if (!ntfId || $btn.hasClass('opacity-50')) {
				return;
			}

			$btn.addClass('opacity-50 pointer-events-none');

			$.ajax({
				type: 'POST',
				url: (typeof ajaxurl !== 'undefined') ? ajaxurl : (typeof fed_object !== 'undefined' ? fed_object.ajax_url : '/wp-admin/admin-ajax.php'),
				data: {
					action: 'fed_ntf_toggle_status',
					id: ntfId,
					fed_nonce: nonce
				},
				dataType: 'json',
				success: function(response) {
					$btn.removeClass('opacity-50 pointer-events-none');
					if (response.success) {
						var newStatus = response.data.status;
						if (newStatus === 'Enable') {
							$btn.removeClass('bg-slate-100 text-slate-500 border-slate-200 hover:bg-slate-200')
								.addClass('bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100');
							$btn.find('.fed-ntf-status-dot').removeClass('bg-slate-400').addClass('bg-emerald-500');
							$btn.find('.fed-ntf-status-label').text('Active');
						} else {
							$btn.removeClass('bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100')
								.addClass('bg-slate-100 text-slate-500 border-slate-200 hover:bg-slate-200');
							$btn.find('.fed-ntf-status-dot').removeClass('bg-emerald-500').addClass('bg-slate-400');
							$btn.find('.fed-ntf-status-label').text('Disabled');
						}
					} else {
						alert(response.data.message || 'Error toggling status.');
					}
				},
				error: function() {
					$btn.removeClass('opacity-50 pointer-events-none');
					alert('Server error occurred. Please try again.');
				}
			});
		});

		// -------------------------------------------------------------
		// 3. Admin: Delete Notification with Custom Popup Confirmation
		// -------------------------------------------------------------
		function showFedCustomDeleteModal(title, onConfirm) {
			$('#fed_custom_confirm_modal').remove();

			var modalHtml = '<div id="fed_custom_confirm_modal" class="fixed inset-0 z-[99999] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs transition-opacity duration-200" style="position: fixed; inset: 0; z-index: 99999; display: flex; align-items: center; justify-content: center; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px);">' +
				'<div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200/90 transform scale-95 transition-transform duration-200 space-y-4" style="background: #ffffff; border-radius: 1.5rem; max-width: 28rem; width: 100%; padding: 1.5rem; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); border: 1px solid #e2e8f0;">' +
					'<div style="display: flex; align-items: flex-start; gap: 0.875rem;">' +
						'<div style="width: 2.75rem; height: 2.75rem; border-radius: 1rem; background: #fff1f2; color: #e11d48; display: flex; align-items: center; justify-content: center; font-size: 1.125rem; flex-shrink: 0; border: 1px solid #ffe4e6;">' +
							'<i class="fas fa-trash-alt"></i>' +
						'</div>' +
						'<div style="flex: 1; min-width: 0;">' +
							'<h4 style="font-size: 0.875rem; font-weight: 700; color: #0f172a; margin: 0;">Delete Notification?</h4>' +
							'<p style="font-size: 0.75rem; color: #64748b; margin: 0.25rem 0 0 0; line-height: 1.4;">Are you sure you want to delete <strong style="color: #1e293b;">"' + $('<div>').text(title).html() + '"</strong>? This action cannot be undone.</p>' +
						'</div>' +
					'</div>' +
					'<div style="display: flex; align-items: center; justify-content: flex-end; gap: 0.625rem; padding-top: 0.75rem; border-top: 1px solid #f1f5f9;">' +
						'<button type="button" id="fed_modal_cancel_btn" style="padding: 0.5rem 1rem; border-radius: 0.75rem; font-size: 0.75rem; font-weight: 600; background: #f1f5f9; color: #334155; border: none; cursor: pointer; transition: all 0.2s;">' +
							'Cancel' +
						'</button>' +
						'<button type="button" id="fed_modal_confirm_btn" style="padding: 0.5rem 1.25rem; border-radius: 0.75rem; font-size: 0.75rem; font-weight: 700; background: #e11d48 !important; color: #ffffff !important; border: none; cursor: pointer; transition: all 0.2s; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">' +
							'Yes, Delete' +
						'</button>' +
					'</div>' +
				'</div>' +
			'</div>';

			$('body').append(modalHtml);
			var $modal = $('#fed_custom_confirm_modal');

			$modal.on('click', '#fed_modal_cancel_btn', function() {
				$modal.fadeOut(150, function() { $(this).remove(); });
			});

			$modal.on('click', '#fed_modal_confirm_btn', function() {
				$modal.fadeOut(150, function() { $(this).remove(); });
				if (typeof onConfirm === 'function') {
					onConfirm();
				}
			});

			$modal.on('click', function(e) {
				if ($(e.target).is('#fed_custom_confirm_modal')) {
					$modal.fadeOut(150, function() { $(this).remove(); });
				}
			});
		}

		$(document).on('click', '.fed-ntf-delete-btn', function(e) {
			e.preventDefault();
			var $btn = $(this);
			var ntfId = $btn.data('id');
			var title = $btn.data('title') || 'this notification';
			var nonce = $btn.data('nonce');

			if (!ntfId) {
				return;
			}

			var executeDelete = function() {
				var $row = $('#fed_ntf_row_' + ntfId);
				$row.css('opacity', '0.4');

				$.ajax({
					type: 'POST',
					url: (typeof ajaxurl !== 'undefined') ? ajaxurl : (typeof fed_object !== 'undefined' ? fed_object.ajax_url : '/wp-admin/admin-ajax.php'),
					data: {
						action: 'fed_ntf_delete_notification',
						id: ntfId,
						fed_nonce: nonce
					},
					dataType: 'json',
					success: function(response) {
						if (response.success) {
							if (typeof fedAdminAlert !== 'undefined' && fedAdminAlert.showToast) {
								fedAdminAlert.showToast(response.data.message || 'Notification deleted.', false);
							}
							$row.fadeOut(300, function() {
								$(this).remove();
								if ($('#fed_ntf_table tbody tr').length === 0) {
									window.location.reload();
								}
							});
						} else {
							$row.css('opacity', '1');
							if (typeof fedAdminAlert !== 'undefined' && fedAdminAlert.showToast) {
								fedAdminAlert.showToast(response.data.message || 'Error deleting notification.', true);
							} else {
								alert(response.data.message || 'Error deleting notification.');
							}
						}
					},
					error: function() {
						$row.css('opacity', '1');
						if (typeof fedAdminAlert !== 'undefined' && fedAdminAlert.showToast) {
							fedAdminAlert.showToast('Server error occurred. Please try again.', true);
						} else {
							alert('Server error occurred. Please try again.');
						}
					}
				});
			};

			if (typeof swal === 'function') {
				var swalResult = swal({
					title: 'Delete Notification?',
					text: 'Are you sure you want to delete "' + title + '"? This action cannot be undone.',
					type: 'warning',
					showCancelButton: true,
					confirmButtonColor: '#e11d48',
					cancelButtonColor: '#64748b',
					confirmButtonText: 'Yes, Delete',
					cancelButtonText: 'Cancel'
				});

				if (swalResult && typeof swalResult.then === 'function') {
					swalResult.then(function(result) {
						if (result === true || (result && result.value) || (result && !result.dismiss)) {
							executeDelete();
						}
					}, function() {
						// dismissed
					});
				} else if (typeof swalResult === 'undefined') {
					// Fallback for callback-based swal
					swal({
						title: 'Delete Notification?',
						text: 'Are you sure you want to delete "' + title + '"? This action cannot be undone.',
						type: 'warning',
						showCancelButton: true,
						confirmButtonColor: '#e11d48',
						cancelButtonColor: '#64748b',
						confirmButtonText: 'Yes, Delete',
						cancelButtonText: 'Cancel'
					}, function(isConfirm) {
						if (isConfirm) {
							executeDelete();
						}
					});
				}
			} else {
				showFedCustomDeleteModal(title, executeDelete);
			}
		});

	});
})(jQuery);
