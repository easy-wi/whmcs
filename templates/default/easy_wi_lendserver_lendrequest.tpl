{if $return->status ne "started" && $return->started ne "started"}
    <div class="alert alert-warning">
        <strong>{$return->error}</strong>
    </div>
{else}
    <div class="alert alert-info">
        <strong>{$easy_wi_lang.lendSuccess}</strong>
    </div>
    <table class="table table-striped table-bordered table-hover">
        {if $type eq "gs"}
            <thead>
            <tr>
                <th>{$easy_wi_lang.ip}:{$easy_wi_lang.port}</th>
                <th>{$easy_wi_lang.slots}</th>
                <th>{$easy_wi_lang.lendTime}</th>
                <th>RCON {$easy_wi_lang.password}</th>
                <th>{$easy_wi_lang.password}</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td>{$return->ip}:{$return->port}</td>
                <td>{$return->slots}</td>
                <td>{$return->lendtime}</td>
                <td>{$return->rcon}</td>
                <td>{$return->password}</td>
            </tr>
            </tbody>
        {else}
            <thead>
            <tr>
                {if $return->dns ne ""}
                    <th>{$easy_wi_lang.dns}</th>
                {else}
                    <th>{$easy_wi_lang.ip}:{$easy_wi_lang.port}</th>
                {/if}
                <th>{$easy_wi_lang.slots}</th>
                <th>{$easy_wi_lang.lendTime}</th>
                <th>{$easy_wi_lang.password}</th>
                <th>Token</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                {if $return->dns ne ""}
                    <td><a href="ts3server://{$return->dns}?password={$return->password}">{$return->dns}</a></td>
                {else}
                    <td><a href="ts3server://{$return->ip}:{$return->port}?password={$return->password}">{$return->ip}:{$return->port}</a></td>
                {/if}
                <td>{$return->slots}</td>
                <td>{$return->lendtime}</td>
                <td>{$return->password}</td>
                <td>{$return->token}</td>
            </tr>
            </tbody>
        {/if}
    </table>
{/if}
