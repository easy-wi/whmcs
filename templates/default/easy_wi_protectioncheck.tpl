{if $inputHighlighting eq "error"}
    <div class="alert">
        <strong>{$easy_wi_lang.protectionError}</strong><br />
        {$easy_wi_lang.gameName}: {$protectionStatus->gametype}<br />
        {$easy_wi_lang.hostname}: {$protectionStatus->hostname}<br />
    </div>
{elseif $inputHighlighting eq "success"}
    <div class="alert alert-success">
        <strong>{$easy_wi_lang.protectionOk}</strong><br />
        {$easy_wi_lang.gameName}: {$protectionStatus->gametype}<br />
        {$easy_wi_lang.hostname}: {$protectionStatus->hostname}<br />
    </div>
{elseif $inputHighlighting eq "warning"}
    <div class="alert alert-warning">
        <strong>{$easy_wi_lang.protectionUnknownState}</strong>
    </div>
{else}
    <div class="alert alert-info">
        <strong>{$easy_wi_lang.protectionInfo}</strong>
    </div>
{/if}
<form method="post" action="protectioncheck.php" class="form-horizontal">
    <div class="form-group">
        <label for="inputAddress" class="col-sm-4 control-label">{$easy_wi_lang.address}</label>
        <div class="col-sm-6">
        	<input type="text" id="inputAddress" class="form-control" name="address" value="{$address}" placeholder="1.1.1.1:27015" required="required" pattern="(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\:([1-9]|[1-9][0-9]|[1-9][0-9][0-9]|[1-9][0-9][0-9][0-9]|[1-5][0-9][0-9][0-9][0-9]|[6][0-4][0-9][0-9][0-9]|[6][5][0-5][0-9][0-9])">
        </div>
    </div>
    <div class="text-center">
        <button type="submit" name="msettings" class='btn btn-primary'><i class="fa fa-search"></i> {$easy_wi_lang.check}</button>
    </div>
</form>
