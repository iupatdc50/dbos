// noinspection JSUnusedGlobalSymbols
/**
 * Used to refresh create and update receipt toolbars after any amount is added or modified.
 *
 * The HTML sets up like this:
 *
 * <div id="tallies" data-url=route>
 *   <div id="running-display" class="pull-right">
 *     <div id="running-total" class="flash-success"></div>
 *   </div>
 *   <div id="balance-display" class="pull-right">
 *     <div id="out-of-balance" class="flash-error"></div>
 *   </div>
 * </div>
 *
 * <div>
 *   <p>
 *       <button id="post-btn>..</button>
 *       <button id="balance-btn">..</button>
 *       ... (other buttons)
 *   </p>
 * </div>
 *
 * Notes
 *   o  data-url holds the route to the /balances-json action, which returns 2 values: "balance" & "running"
 *   o  "post-btn" and "balance-btn" can be assigned to an html <button>, <a href> or <input type=submit">
 *
 */
function refreshToolBar() {
    $.getJSON($("#tallies").attr("data-url"), function(data) {
        // noinspection EqualityComparisonWithCoercionJS
        if (data["balance"] == 0.00) {
            $('#balance-display').hide();
            $('#balance-btn').hide();
            $('#post-btn').show();
        } else {
            $('#out-of-balance').html("Out of Balance: " + data["balance"]);
            $('#balance-display').show();
            $('#balance-btn').show();
            $('#post-btn').hide();
        }
        $('#running-total').html("Total Allocation: " + data["running"]);
    });
}

