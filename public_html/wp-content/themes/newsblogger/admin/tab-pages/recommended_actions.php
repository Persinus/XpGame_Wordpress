<?php 
	$newsblogger_actions = get_query_var( 'newsblogger_actions', [] );
    $newsblogger_actions_todo = get_query_var( 'newsblogger_actions_todo', [] );
?>
<div id="recommended_actions" class="newsblogger-tab-pane panel-close">
	<div class="action-list">
		<?php 
		if($newsblogger_actions): 
			foreach ($newsblogger_actions as $key => $newsblogger_val): ?>
					<div class="action spice-col-3" id="<?php echo esc_attr($newsblogger_val['id']); ?>">
						<div class="action-box">
							<div class="action-watch">
							<?php if(!$newsblogger_val['is_done']): ?>
								<?php if(!isset($newsblogger_actions_todo[$newsblogger_val['id']]) || !$newsblogger_actions_todo[$newsblogger_val['id']]): ?>
									<span class="dashicons dashicons-visibility"></span>
								<?php else: ?>
									<span class="dashicons dashicons-hidden"></span>
								<?php endif; ?>
							<?php else: ?>
								<span class="dashicons dashicons-yes"></span>
							<?php endif; ?>
							</div>
							<div class="action-inner">
								<h3 class="action-title"><?php echo esc_html($newsblogger_val['title']); ?></h3>
								<div class="action-desc"><?php echo esc_html($newsblogger_val['desc']); ?></div>
								<?php echo wp_kses_post($newsblogger_val['link']); ?>
							</div>
						</div>
					</div>
				<?php endforeach; 
		endif; ?>
	</div>
</div>