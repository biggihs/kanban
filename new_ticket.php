  <div id="new_ticket" title="Create a new ticket" style="display:none;">
    <div>
      <div>
        <div>
          Summary
        </div>
        <div>
          <input type="textbox" id="summary" style="width:99%;"/>
        </div>
      </div>
      <div>
        <div>
          Additional Information
        </div>
        <div>
          <textarea id="description" style="width:99%;height:150px;"></textarea>
        </div>
      </div>
      <div class="properties">
        <div>
          <div class="property">
            <div>
              Type
            </div>
            <div>
              <select id="ticket_type">
                <option value="bug" selected="selected">Bug</option>
                <option value="enhancement">Enhancement</option>
                <option value="task">Task</option>
              </select>
            </div>
          </div>
          <div class="property">
            <div>
              Category
            </div>
            <div>
              <select id="category_id">
                <option value=""></option>
              </select>
            </div>
          </div>
        </div>
        <div style="clear:both;"></div>
        <div>
          <div class="property">
            <div>
              Priority
            </div>
            <div>
              <select id="priority_id">
              </select>
            </div>
          </div>
          <div class="property">
            <div>
              Status
            </div>
            <div>
              <select id="status_id">
              </select>
            </div>
          </div>
        </div>
        <div style="clear:both;"></div>
        <div class="assignee">
          <div>
            Assignee
          </div>
          <div>
            <div class="assignee_property" id="assignee_select">
              <div class="image">
                <img class="gravatar" src="/images/Octocat_32.png" title="Assign ticket" />
              </div>
              <div class="name">
                <label id="assignee_new_name">No one is assigned</label>
              </div>
            </div>
            <div style="clear:both;"></div>
          </div>
        </div>
        <div style="clear:both;"></div>
      </div>
      <div class="error_message">
        <label>This is a error message</label>
      </div>
      <div>
        <input type="hidden" id="milestone_id"/>
        <input type="hidden" id="assignee_id"/> 
        <div>
          <div class="button">
            <input type="button" id="create_ticket" value="Create Ticket" />
          </div>
          <div class="button">
            <input type="button" id="cancel_ticket" value="Cancel" />
          </div>
        </div>
      </div>
    </div>
  </div>
  <script>
    jQuery(document).ready(function(){
      jQuery('#new_ticket #create_ticket').click(function(){
        params = {'f':'create_ticket'};
        jQuery('#new_ticket input[type!="button"],#new_ticket select,#new_ticket textarea')
              .each(function(i,j)
                {
                   var input = jQuery(j);
                   params[input.attr('id')] = input.val();
                });
        jQuery.ajax({url:'api.php',
                   data:params,
                success:function(data)
                        {
                            var ticket = JSON.parse(data);
                            if(ticket.error == undefined)
                            {
                               addTicket(ticket);
                               jQuery('#new_ticket').dialog('close');
                               var key = '#ticket-'+ticket['ticket-id'];
                               jQuery(key).fadeOut('fast');
                               jQuery(key).fadeIn('slow');
                            }
                            else
                            {
                               alert(ticket.error);
                            }
                        }
                   });
      });
    });
  </script>
