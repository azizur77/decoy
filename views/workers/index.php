<h1>Workers
	<small>Monitor whether workers are running or not.  The logic of a failed worker is still executed regularily, just at a slower interval.</small>
</h1>

<ul id="workers" class="unstyled">
	
	<? foreach($workers as $worker): ?>
		<li>
			
			<div class="pull-right actions">
				<span class="status <?=$worker->is_running()?'ok':'fail'?>">Rate: <strong><?=$worker->current_interval()?></strong></span>
				<a class="btn">Logs</a>
			</div>
			
			<h3><?=$worker->title()?></h3>
			<? if ($worker->description()): ?>
				<p><?=$worker->description()?></p>
			<? endif ?>
			
			<ul>
				<li>Last worker execution: <?=$worker->last_heartbeat()?></li>
				<li>Last heartbeat (and execution): <?=$worker->last_heartbeat_check()?></li>
				<li>Currently executing every: <?=$worker->current_interval()?></li>
			</ul>
			
			<div class="logs hide"></div>
		</li>
	<? endforeach ?>
	
</ul>