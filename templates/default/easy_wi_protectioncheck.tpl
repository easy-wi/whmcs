<div>
    <div class="row">
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
                <strong>{$easy_wi_lang.protectionInfo} {lang key="gameName"}</strong>
            </div>
        {/if}
    </div>
    <div class="row">
        <form method="post" action="protectioncheck.php" class="form-horizontal">

            <div class="control-group {$inputHighlighting}">
                <label class="control-label" for="inputAddress">{$easy_wi_lang.address}</label>
                <div class="controls">
                    <input type="text" id="inputAddress" name="address" value="{$address}" placeholder="1.1.1.1:27015" required="required" pattern="(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\:([1-9]|[1-9][0-9]|[1-9][0-9][0-9]|[1-9][0-9][0-9][0-9]|[1-5][0-9][0-9][0-9][0-9]|[6][0-4][0-9][0-9][0-9]|[6][5][0-5][0-9][0-9])">
                </div>
            </div>
            <div class="control-group">
                <div class="controls">
                    <button type="submit" name="msettings" class='btn btn-primary'><i class="icon-search icon-white"></i> {$easy_wi_lang.check}</button>
                </div>
            </div>
        </form>
    </div>
</div>