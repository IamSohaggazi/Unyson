<?php if (!defined('FW')) die('Forbidden');
/**
 * @var array $options
 * @var array $values
 * @var string $focus_tab_input_name
 * @var string $reset_input_name
 */
?>
<!--
wp moves flash message error with js after first h2
if there are no h2 on the page it shows them wrong
-->
<h2 class="fw-hidden"></h2>

<?php echo fw()->backend->render_options($options, $values); ?>

<!-- focus tab -->
<?php
$focus_tab_id = trim( FW_Request::POST($focus_tab_input_name, FW_Request::GET($focus_tab_input_name, '')) );
echo fw_html_tag('input', array(
	'type'  => 'hidden',
	'name'  => $focus_tab_input_name,
	'value' => $focus_tab_id,
));
?>
<script type="text/javascript">
jQuery(function($){
	fwEvents.one("fw:options:init", function(){
		var $form = $('form[data-fw-form-id="fw_settings"]:first');

		$form.on("click", ".fw-options-tabs-wrapper > .fw-options-tabs-list > ul > li > a", function(){
			$form.find("input[name='<?php echo esc_js($focus_tab_input_name); ?>']").val(
				$(this).attr("href").replace(/^\\#/, "") // tab id
			);
		});

		/* "wait" after tabs initialized */
		setTimeout(function(){
			var focusTabId = $.trim("<?php echo esc_js($focus_tab_id) ?>");

			if (!focusTabId.length) {
				return;
			}

			var $tabLink = $(".fw-options-tabs-wrapper > .fw-options-tabs-list > ul > li > a[href=\'#"+ focusTabId +"\']");

			while ($tabLink.length) {
				$tabLink.trigger("click");
				$tabLink = $tabLink
					.closest(".fw-options-tabs-wrapper").parent().closest(".fw-options-tabs-wrapper")
					.find("> .fw-options-tabs-list > ul > li > a[href=\'#"+ $tabLink.closest(".fw-options-tab").attr("id") +"\']");
			}

			// click again on focus tab to update the input value
			$(".fw-options-tabs-wrapper > .fw-options-tabs-list > ul > li > a[href=\'#"+ focusTabId +"\']").trigger("click");;
		}, 200);
	});
});
</script>
<!-- end: focus tab -->

<!-- reset warning -->
<script type="text/javascript">
	jQuery(function($){
		$('form[data-fw-form-id="fw_settings"] input[name="<?php echo esc_js($reset_input_name) ?>"]').on('click.fw-reset-warning', function(e){
			/**
			 * on confirm() the submit input looses focus
			 * fwForm.isAdminPage() must be able to select the input to send it in _POST
			 * so use alternative solution http://stackoverflow.com/a/5721762
			 */
			{
				$(this).closest('form').find('input[type="submit"]').removeAttr('clicked');
				$(this).attr('clicked', '');
			}

			if (!confirm('<?php
				echo esc_js(__("Click OK to reset.\nAll settings will be lost and replaced with default settings!", 'fw'))
			?>')) {
				e.preventDefault();
			}
		});
	});
</script>
<!-- end: reset warning -->

<!-- ajax submit -->
<script type="text/javascript">
	jQuery(function ($) { return;
		function generateFlashMessagesHtml(flashMessages) {
			var html = [],
				typeHtml = [],
				messageClass = '',
				iconClass = '';

			jQuery.each(flashMessages, function(type, messages){
				typeHtml = [];

				switch (type) {
					case 'error':
						messageClass = 'fw-text-danger';
						iconClass = 'dashicons dashicons-dismiss';
						break;
					case 'warning':
						messageClass = 'fw-text-warning';
						iconClass = 'dashicons dashicons-no-alt';
						break;
					case 'success':
						messageClass = 'fw-text-success';
						iconClass = 'dashicons dashicons-yes';
						break;
					case 'info':
						messageClass = 'fw-text-info';
						iconClass = 'dashicons dashicons-info';
						break;
					default:
						messageClass = '';
						iconClass = '';
				}

				jQuery.each(messages, function(messageId, message){
					typeHtml.push('<li><h3 class="'+ messageClass +'"><span class="'+ iconClass +'"></span> '+ message +'</h3></li>');
				});

				if (typeHtml.length) {
					html.push(
						'<ul>'+ typeHtml.join('</ul><ul>') +'</ul>'
					);
				}
			});

			return html.join('');
		}

		fwForm.initAjaxSubmit({
			selector: 'form[data-fw-form-id="fw_settings"]',
			loading: function(show) {
				if (show) {
					fw.soleModal.show(
						'fw-options-ajax-save-loading',
						'<p class="fw-text-center"><img src="'+ fw.img.loadingSpinner +'" ></p>',
						{hide: -30000}
					);
				} else {
					fw.soleModal.hide('fw-options-ajax-save-loading');
				}
			},
			onSuccess: function($form, ajaxData) {
				fw.soleModal.show(
					'fw-options-ajax-save-success',
					generateFlashMessagesHtml(ajaxData.flash_messages),
					{hide: 3000}
				);
			}
		});
	});
</script>
<!-- end: ajax submit -->
