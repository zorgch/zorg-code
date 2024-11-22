{assign var='tpl_prefix' value="bugtracker"}
{assign var='tpl_partials' value="layout/partials/`$tpl_prefix`/"}
{include file="file:`$tpl_partials``$tpl_prefix`_head.tpl"}
<link rel="stylesheet" type="text/css" href="css/simple-grid.min.css" />
<div class="container">
	<div class="row">
		<div class="col-3">
			<!-- This content will take up 3/12 (or 1/4) of the container -->
			<h2>Bugtracker</h2>
		</div>
		<div class="col-3">
			<!-- This content will take up 3/12 (or 1/4) of the container -->
		</div>
		<div class="col-6">
			<!-- This content will take up 6/12 (or 1/2) of the container -->
		</div>
	</div>
</div>
