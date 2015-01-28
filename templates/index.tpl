<hr/>
{if $smarty.session.domain_blocker}
    <div class='alert {if $smarty.session.domain_blocker.status > 0}alert-success{else} alert-error{/if}'>
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        {'<br/>'|implode:$smarty.session.domain_blocker.msg} 
    </div>
{/if}
{php} unset($_SESSION['domain_blocker']); {/php}
<div class="strings-container">
    <h3>Block patterns</h3>
    {if $block_strings|@count != 0}
        <a href="javascript:void(0);" class="btn_action btn" data-action="activate_pattern">Activate</a>
    {/if}
    <a href="javascript:void(0);" class="btn_action btn btn-success" data-action="add_pattern">New Pattern</a>
    <table class="table table-bordered table-hover" style="margin-top:20px;">
        <thead>
            <th></th>
            <th>Pattern</th>
            <th>Type</th>
            <th>Activated</th>
            <th>Actions</th>
        </thead>
        <tbody>
            {if $block_strings|@count == 0}
                <tr><td colspan="4">No block strings found.</td></tr>
            {else}
                {foreach from=$block_strings item=val key=k}
                    <tr>
                        <td style="width:20px;text-align:center;" class="pattern_activation"><input type="checkbox" name="data[pattern][{$val.id}]" data-value='{$val.id}'/></td>
                        <td>{$val.pattern}</td>
                        <td>{$val.pattern_type}</td>
                        <td>{if $val.activated == 1}<span class="label">Yes</span>{else}<span class="label label-important">No</label>{/if}</td>
                        <td>
                            <div class="btn-group">
                                <button class="btn btn_action" data-action="edit_pattern" data-id="{$val.id}">Edit</button>
                                <button class="btn btn-danger btn_action" data-action="delete_pattern" data-id="{$val.id}">Delete</button>
                            </div>
                        </td>
                    </tr>
                {/foreach}
            {/if}
        </tbody>
    </table>
{if $block_strings|@count != 0}
    <a href="javascript:void(0);" class="btn_action btn" data-action="activate_pattern">Activate</a>
{/if}
    <a href="javascript:void(0);" class="btn_action btn btn-success" data-action="add_pattern">New Pattern</a>
</div>
<br/><br/>
<hr/>


<div class="domains-container">
    <h3>Blocked domains</h3>
    <table class="table table-bordered table-hover">
        <thead>
            <th>Domain</th>
            <th>User IP</th>
            <th>Attempts</th>
            <th>Last Attempt</th>
        </thead>
        <tbody>
            {if $blocked_domains|@count == 0}
                <tr><td colspan="3">No Blocked domains found.</td></th>
            {else}
                {foreach from=$blocked_domains item=val key=k}
                    <tr>
                        <td>{$val.domain}</td>
                        <td>{$val.ipaddress}</td>
                        <td>{$val.attempts}</td>
                        <td>{$val.modified}</td>
                    </tr>
                {/foreach}
            {/if}
        </tbody>
    </table>
</div>

<div class="modal hide fade patternModal">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h3>Modify Blacklist Pattern</h3>
    </div>
    <!-- whatever is needed comes from AJAX response -->
</div>
