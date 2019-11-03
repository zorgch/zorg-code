{if $smarty.session.query_track_previous_page}
  {*assign var=queries_untracked value=$smarty.session.num_querys_previous_page-$smarty.session.num_querys_tracked_previous_page*}
  {*assign var=queries_untracked value=$smarty.session.query_request[$smarty.get.analyze].num_query_tracked-$smarty.session.num_querys_tracked_previous_page*}
  <div style="text-align:left">
      <h3>Hier die Infos der SQL-Querys der vorherigen Page</h3>
      {*<ul><li>Request: <a href="{$smarty.session.query_request_previous_page}"><code>{$smarty.session.query_request_previous_page}</code></a></li>
      <li>Total Queries:<strong> {$smarty.session.num_querys_previous_page}</strong></li></ul>*}
      <ul><li>Request: <a href="{$smarty.session.query_request_previous_page}"><code>{$smarty.session.query_request_previous_page}</code></a></li>
      <li>Total Queries:<strong> {$smarty.session.query_request.page[$smarty.get.analyze].num_querys_previous_page}</strong></li></ul>
  </div>
  {if $queries_untracked < 0}
    <div class="alert info">
      <strong>Oops do stimmt was nöd</strong><br>
      untracked Queries = {$queries_untracked}?! Bitte d'Page mol neu lade!
    </div>
  {*else}
    {if $queries_untracked > 0}{$queries_untracked} Queries wurden vor dem Laden der $user-Variable abgesetzt und wurden deshalb nicht aufgezeichnet.{/if*}
  {/if}
  <hr>
  <h4>Query Trace</h4>
  {assign var=queryoutnum value=$smarty.session.num_querys_previous_page-$smarty.session.num_querys_tracked_previous_page+1}

  {foreach from=$smarty.session.query_track_previous_page key=page item=queries name=pages}
  {*if $page == $smarty.get.analyze*}
  <table class="forum">
    <tbody>
      <tr>
        <td align="left" class="border forum">
          <table bgcolor="141414" style="table-layout:fixed;" width="100%">
            <tbody>
              <tr style="font-size: x-small;">
                <td class="forum">
  	              <h5>{$page}</h5>
                </td>
              </tr>
            </tbody>
          </table>
        </td>
      </tr>
    </tbody>
  </table>
  <div>
    {foreach from=$queries key=i item=query name=page_queries}
    {if $queryfile neq $query.file}
    <table class="forum">
      <tbody>
        <tr>
          {if not $smarty.foreach.page_queries.last}<td class="vertline">
            <img class="forum" src="/images/forum/night/split.gif">
          </td>
          {else}<td class="end"></td>{/if}
          <td align="left" class="border forum">
            <table bgcolor="0A0A0A" style="table-layout:fixed;" width="100%">
              <tbody>
                <tr style="font-size: x-small;">
  	              <td class="forum">
  		              {$last_page}<a href="#{$queryoutnum}" name="{$queryoutnum}">#{$queryoutnum}</a> {$query.file}
  		            </td>
                </tr>
              </tbody>
            </table>
          </td>
        </tr>
      </tbody>
    </table>{/if}
    <div>
      <table class="forum">
        <tbody>
          <tr>
            <td class="{if not $smarty.foreach.page_queries.last}vertline{else}space{/if}"></td>
            {if not $smarty.foreach.page_queries.last}<td class="vertline"><img class="forum" src="/images/forum/night/split.gif"></td>{else}<td class="end"></td>{/if}
            <td align="left" class="forum">
              <table bgcolor="0A0A0A" style="table-layout:fixed;" width="100%">
                <tbody>
                  <tr style="font-size: x-small;">
                    <td class="forum">
  	                  Line {$query.line}: <code>&lt;{$query.function}&gt;</code>
                    </td>
                  </tr>
                </tbody>
              </table>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    {assign var=queryfile value=$query.file}
    {assign var=queryoutnum value=$queryoutnum+1}
    {/foreach}
  </div>
  {*/if*}
  {/foreach}
{else}
  Im Moment werden für dich keine Tracks gespeichert. Du kannst das in der User-Table einstellen (sql_tracker).
{/if}
