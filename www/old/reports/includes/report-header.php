<h2 class="report-header"><span onclick="show_report( '<?= strtolower( str_replace( ' ', '_', $report )); ?>' );"><?= $report; ?></span></h2>
<form action="/reports/enabled.d/<?= strtolower( str_replace( ' ', '_', $report )); ?>.php" method="post">
	<div id="container-<?= strtolower( str_replace( ' ', '_', $report )); ?>" class="report-container" style="display: none;"></div>
	<input type="hidden" name="task" value="execute">
</form>
