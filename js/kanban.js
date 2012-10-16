var statuses;
var milestones;
var categories = {};
var priorities = {};
var users = {};
var activeMilestones;
var currentMilestone;
var tickets = [];
var ciBranches = {};

var category_limits = {
                        "Accepted" : [2,4],
                        "In Progress" : [2,4],
                        "Ready To Deploy" : [1,3]
                      };

function reloadOverview(){
  var str = "#status-";
  jQuery('#overview a').each(function(i,a){
    var anchor = jQuery(a);
    var key = anchor.attr('href').replace(str,'');
    var ticket_count = jQuery('#status-'+key+' .ticket').length;
    var tmp = anchor.html().split('(')[0];
    anchor.html(tmp+"("+ticket_count+")");
  });
}

function addRatio(){
    jQuery('.status').each(function(i,j){
      var count = jQuery(this).find('.ticket').length; 
      var key = category_limits[jQuery(this).find('h2').last().html()];
      if (key==undefined)
        if(i==0)
        {
          jQuery(this).find('.ratio').html("<img id='create_new_ticket' style='vertical-align:bottom;width:20px;' src='images/new.png' title='Create new ticket' />").attr('style','padding-top:3px;background-color:white;');
          jQuery('#create_new_ticket').click(function(){jQuery('#new_ticket').dialog({modal:true,width:'500px'});});
        }
        else
          jQuery(this).find('.ratio').html("&nbsp;").attr('style','padding-top:7px;background-color:white;');
      else
      {
        var ratio_count = "<span>("+count+"/"+key[1]+")</span>";
        var ratio = jQuery(this).find('.ratio');
        var src = "";

        if(key[0]>count)
          src = "blue_thumb_right.jpg";
        else if(key[1]>count)
          src = "green_thumb_right.jpg";
        else
          src = "stop.png";

        ratio.html("<span style='font-size:15px;'>"+ratio_count+"<span><img style='vertical-align:bottom;width:20px;' src='images/"+src+"'/></span></span>");
      }
    });
}

function addDraggableDropable(){
  jQuery('#open .ticket').draggable( 
                                    { 
                                      containment: "#body", 
                                           helper: "clone"
                                    }
                                   );

  jQuery('#open .status').droppable( {
                               accept:".ticket",
                           hoverClass:"droppable_selected",
                                 drop:function(ev,ui,db)
                                 {
                                   var container = jQuery(this);
                                   var status_key = container.find('h2').last().html();
                                   var count = container.find('.ticket').length;
                                   if((category_limits[status_key] != undefined)
                                   &&((category_limits[status_key][1]) < (count+1))) {
                                     alert('The maximum ammount of tickets in this category is :: '+category_limits[status_key][1]+' !!!');
                                   }
                                   else {
                                     var ticket_id = ui.draggable.context.id.replace('ticket-','');
                                     var status_id = container.attr('id').replace('status-','');
                                     var post = {'f':'update_ticket','ticket_id':ticket_id,'status_id':status_id};

                                     $.get('/api.php',post,function (data) {
                                       container.append(jQuery(ui.draggable).clone());
                                       jQuery(ui.draggable).remove();
                                       addRatio();
                                       reloadOverview();

                                     });

                                   }
                                  }
                               });
}

function loadTickets(pageNumber, ticketStatus) {
    var ticketUrl = '/api.php?f=tickets&s=' + ticketStatus + '&q=' + escape(currentMilestone.name);
/*
 *  The Codebase.php has been modified to get all the pages
    if (pageNumber > 1) {
        ticketUrl = ticketUrl + '&p=' + pageNumber;
    }
*/
    $.get(ticketUrl, function (data) {

        //If there are no tickets of this type, then return.
        if(data.ticket == undefined)
          return; 

        if(data.ticket.length == undefined) //there is only one ticket obj. Not a ticket array.
          tickets.push(data.ticket);
        else
          $.each(data.ticket, function(i, ticket) {
            tickets.push(ticket);
          });

        processTickets(pageNumber, ticketStatus, data.ticket.length);

        if (ticketStatus == "open")
        {
          addRatio();
          addDraggableDropable();
        }
    }, 'json');
}

function processTickets(pageNumber, ticketStatus, totalTickets) {
    var newTickets = 0;

    $.each(tickets, function(i, ticket) {
        if ($('#ticket-' + ticket['ticket-id']).size() == 0) {
            newTickets++;
            addTicket(ticket);
        }
    });

    countTickets();

    if ((totalTickets == 30) && (newTickets > 0)) {
        loadTickets(pageNumber + 1, ticketStatus);
    }

    jQuery('#open#'+ticketStatus+' .gravatar').each(function(){
      var id = jQuery(this).id;
      jQuery(this).qtip({
          content: { prerender: true,
                          text: create_user_assign_list(jQuery(this)),
                         title: "Assign new user ..."
                   },
             show: { delay: 0 },
             hide: { fixed: true },
         position: { corner: { target: 'topRight', tooltip: 'topLeft' } },
            style: { border: { width: 1, color: '#666'} },
      });
    });
}

/***
  * Parse the date for browser compatibility, see
  * http://stackoverflow.com/questions/4622732/new-date-using-javascript-in-safari
 ***/
function parseDate(input) {
	var parts = input.match(/(\d+)-(\d+)-(\d+)T(\d+):(\d+):(\d+)(Z|\+|-)((\d+):(\d+))?/);
	
	var timezoneOffset = 0;
	if (parts[7] == '+') {
	    timezoneOffset = -(parseInt(parts[9], 10) * 60 + parseInt(parts[10], 10));
    } else if (parts[7] == '-') {
    	timezoneOffset = parseInt(parts[9], 10) * 60 + parseInt(parts[10], 10);
    }
	return new Date(parts[1], parts[2]-1, parts[3], parts[4], parseInt(parts[5],10)+timezoneOffset-(new Date()).getTimezoneOffset(), parts[6]);
}

function calcTimeAgo(date) {
	// Date is returned as UTC time, so we have to add our TimezoneOffset as we live in UTC+1 (or +2 with DST)
	var minutesAgo = (new Date().getTime() - date.getTime()) / 60000;
	var timeAgo = new Array();
	if (minutesAgo > 24*60) {
	    timeAgo['long'] = Math.round(minutesAgo/24/60) + " days";
	    timeAgo['short'] = Math.round(minutesAgo/24/60) + "days";
    } else if (minutesAgo > 60) {
        timeAgo['long'] = Math.round(minutesAgo/60) + " hours";
        timeAgo['short'] = Math.round(minutesAgo/60) + "hrs";
    } else {
        timeAgo['long'] = Math.round(minutesAgo) + " minutes";
        timeAgo['short'] = Math.round(minutesAgo) + "min";
    }

	return timeAgo;
}

function get_gravatar_image(hash,username,id)
{
  return $('<img class="'+id+'" src="http://www.gravatar.com/avatar/' + hash + '?s=32" title="' + username + '" />');
}

function assign_user_ticket(user,ticket){
  var post = {'f':'assign_ticket','ticket_id':ticket,'user_id':user};
  $.get('/api.php',post,function (data) {
    u = user;
    if(user != "")
      var img = get_gravatar_image(users[user].hash,users[user]['first-name'],'gravatar');
    else
      var img = $('<img class="gravatar" src="/images/Octocat_32.png" title="Assign ticket" />');
    jQuery('#ticket-'+ticket+' .gravatar').parent().html(img);
    img.qtip({
        content: { prerender: true,
                        text: create_user_assign_list(img),
                       title: "Assign new user ..."
                 },
           show: { delay: 0 },
           hide: { fixed: true },
       position: { corner: { target: 'topRight', tooltip: 'topLeft' } },
          style: { border: { width: 1, color: '#666'} },
    });
  });
}

function add_assignee_to_new_ticket(user_id)
{
  if(user_id == "")
  {
    jQuery('#new_ticket #assignee_id').val("");
    var img = '<img class="gravatar" src="/images/Octocat_32.png" title="Assign ticket" />';
    var name = 'No one is assigned';
    jQuery('#new_ticket #assignee_new_name').html(name);
    jQuery('#new_ticket .image').html(img);
  }
  else
  {
    jQuery('#new_ticket #assignee_id').val(user_id);
    var img = get_gravatar_image(users[user_id].hash,users[user_id]['first-name'],'gravatar');
    var name = users[user_id]["first-name"] + " " + users[user_id]["last-name"];
    jQuery('#new_ticket #assignee_new_name').html(name);
    jQuery('#new_ticket .image').html(img);
  }
}

function create_user_assign_new_list(){
    var keys = Object.keys(users);
    var user_list = jQuery('<ul>').attr('class','users_list')
                                  .attr('style','list-style-type: none;');
    jQuery(keys).each(function(i,j){
      user_list.append($('<li onClick="add_assignee_to_new_ticket(\''+users[j].id+'\')" />').attr('style','cursor:pointer;')
		           .html(
			               $('<div>').html(
				               get_gravatar_image(users[j].hash,users[j].username,users[j].id).attr('style','float:left;')
                                                                                      .add(jQuery("<div>").attr('style','margin-top:5px;')
                                                                                                          .html(users[j].username)))
                             .add(jQuery('<div style="clear:both;">'))
	                  ));
    });
    user_list.prepend($('<li style="cursor:pointer;" onClick="add_assignee_to_new_ticket(\'\')"><div><img class="gravatar" style="float:left;" src="/images/Octocat_32.png" title="Assign ticket" /><div style="margin-top:5px;">Octocat</div></div><div style="clear:both;"></div></li>'));
    return user_list;
}

function create_user_assign_list(the_object){
    var id = jQuery(the_object.closest('.ticket')).attr('id').replace('ticket-','');
    var keys = Object.keys(users);
    var user_list = jQuery('<ul>').attr('class','users_list')
                                  .attr('style','list-style-type: none;');
    jQuery(keys).each(function(i,j){
      user_list.append($('<li onClick="assign_user_ticket('+users[j].id+',' + id + ')" />').attr('style','cursor:pointer;')
		           .html(
			               $('<div>').html(
				               get_gravatar_image(users[j].hash,users[j].username,users[j].id).attr('style','float:left;')
                                                                                      .add(jQuery("<div>").attr('style','margin-top:5px;')
                                                                                                          .html(users[j].username)))
                             .add(jQuery('<div style="clear:both;">'))
	                  ));
    });
    user_list.prepend($('<li style="cursor:pointer;" onClick="assign_user_ticket(\'\',\''+id+'\');"><div><img class="gravatar" style="float:left;" src="/images/Octocat_32.png" title="Assign ticket" /><div style="margin-top:5px;">Octocat</div></div><div style="clear:both;"></div></li>'));
    return user_list;
}

function addTicket(ticket) {
    var ticketTypeClass = 'ticket-default';
    var ticketCategory = categories[ticket['category-id']];
    var ticketPriority = priorities[ticket['priority-id']];
    var ticketId = 'ticket-' + ticket['ticket-id'];
    var gravatarHash = (users[ticket['assignee-id']] !== undefined) ? users[ticket['assignee-id']].hash : '';
    var userName = (users[ticket['assignee-id']] !== undefined) ? users[ticket['assignee-id']]['first-name'] + ' ' + users[ticket['assignee-id']]['last-name'] : '';
	  var ticketSummary = (ticket.summary.length > 50) ? ticket.summary.substr(0, 50) + '...' : ticket.summary;
	  var timeAgo = calcTimeAgo(parseDate(ticket['updated-at']));
    var ticketBranches = ciBranches[ticket['ticket-id']] || {};
    var ciStatus = 'unknown';
    $.each(ticketBranches, function(projectName, branch) {
        if (branch.cake_testsuite_failures == 0 && ciStatus == 'unknown') {
            ciStatus = 'ok';
        } else if (branch.cake_testsuite_failures > 0) {
            ciStatus = 'fail';
        }
    });
	
    /*
	// change color for other repo's
	matches = ticketCategory.match(/^\s*(\w+)\s*[\-|\/]/);
	if (matches) {
	    ticketTypeClass = 'ticket-' + matches[1].toLowerCase();
	}
 */
	
	var bodyDiv = $('<div />').attr('class', 'ticket-body');
    if (gravatarHash != '') {
	bodyDiv.append($('<div>').html(get_gravatar_image(gravatarHash,userName,'gravatar '+ticket['assignee-id'])));
    }
    else{
        bodyDiv.append($('<img class="gravatar" src="/images/Octocat_32.png" title="Assign ticket" />'));
    }
    bodyDiv
        .append($('<abbr class="age" title="updated '+timeAgo['long']+' ago">'+timeAgo['short']+'</abbr>'))
        .append($('<a href="https://'+account_name+'.codebasehq.com/projects/'+project_name+'/tickets/' + ticket['ticket-id'] + '" target="_blank" />').attr('class', 'ticket-link').text('#' + ticket['ticket-id']))
        .append($('<span>'+priorities[ticket['priority-id']].name+'</span>'));
	
	var div = $('<div />')
	    .attr('id', ticketId)
	    .attr('class', 'ticket ' + ticketTypeClass)
	    .attr('style', 'border-top: 2px solid ' + ticketPriority.colour + ';')
	    .append($('<h3 />').attr('title', ticket.summary).text(ticketSummary))
	    .append(bodyDiv);
	
    var tipItems = "";
    $.each(ticketBranches, function(projectName, branch) {
        $.each(branch.urls.logs || [], function(i, url) {
            var urlparts = url.match(/([a-f0-9]{7})[a-f0-9]{30}([a-f0-9]{3}.*$)/);
            tipItems += '<li><a href="'+settings.ciUrl+url+'">'+urlparts[1]+'...'+urlparts[2]+'</a></li>';
        });
    });
    $('#status-' + ticket['status-id']).append(div);
}

function countTickets() {
    $('#overview').html('');
    var overviewList = $('<ul />');
    $.each(statuses, function (i, status) {
        var ticketCount = $('#status-' + status.id).find('.ticket').size();
        var overviewListItem = $('<li />');
        overviewListItem.append($('<a href="#status-' + status.id + '" />').text(status.name + '(' + ticketCount + ')'));
        overviewList.append(overviewListItem);
    });
    $('#overview').append(overviewList);
}


$(document).ready(function() {
    apiPromises = [];
    apiPromises.push($.get('/api.php?f=statuses', function (data) {
        statuses = data['ticketing-status'];
        $.each(statuses, function(i, status) {
    			var statusBox = $('<div class="status" />').attr('id', 'status-' + status.id);
          statusBox = statusBox.append($('<h2 class="ratio"/>').attr('style', 'background-color: #FFFFFF;color:black;'));
          statusBox = statusBox.append($('<h2 />').attr('style', 'background-color: ' + status['colour'] ).text(status.name));
			    var target = (status['treat-as-closed'] == 'true') ? '#closed' : '#open';
			    $(target).append(statusBox);
        });
    }, 'json'));

    apiPromises.push($.get('/api.php?f=users', function (data) {
        $.each(data.user, function (i, user) {
            users[user.id] = user;
        });
    }, 'json'));

    apiPromises.push($.get('/api.php?f=milestones', function (data) {
        milestones = data['ticketing-milestone'];
        //Fix, if you only have one milestone.
        if(!Array.isArray(milestones))
          milestones = [milestones];

        activeMilestones = $.grep(milestones, function(milestone, i) {
          
            return (milestone.status == 'active');
        });
        currentMilestone = activeMilestones[0];
    }, 'json'));

    apiPromises.push($.get('/api.php?f=priorities', function (data) {
        rawPriorities = data['ticketing-priority'];
        $.each(rawPriorities, function (i, priority) {
            priorities[priority.id] = priority;
        });
    }, 'json'));

    apiPromises.push($.get('/api.php?f=categories', function (data) {
        rawCategories = data['ticketing-category'];
        $.each(rawCategories, function (i, category) {
            categories[category.id] = category.name;
        });
    }, 'json'));

    $.when.apply($, apiPromises)
        .done(function(){ loadTickets(1, 'open'); })
        .done(function(){ loadTickets(1, 'closed')})
        .done(function(){
                          //stuff to do after everything has been loaded (the api calls have been made)
                          //Fill in the data from codebase into the new_ticket form (ids for categories etc.)
                          jQuery("#milestone_id").val(currentMilestone.id);
                          var category_list = jQuery('#new_ticket #category_id');
                          jQuery(Object.keys(categories)).each(function(i,j){
                                                            category_list.append(jQuery('<option>').attr('value',j).html(categories[j]));
                                                         })
                          var priority_list = jQuery('#new_ticket #priority_id');
                          jQuery(Object.keys(priorities)).each(function(i,j){
                                                            var option = jQuery('<option>').attr('value',j).html(priorities[j]["name"]);
                                                            if(priorities[j]["default"] == "true")
                                                              option.attr('selected','selected');
                                                            priority_list.append(option);

                                                         })
                          var status_list = jQuery('#new_ticket #status_id');
                          jQuery(Object.keys(statuses)).each(function(i,j){
                                                            if(statuses[j]["treat-as-closed"] != false)
                                                            {
                                                                var option = jQuery('<option>').attr('value',statuses[j]["id"]).html(statuses[j]["name"]);
                                                                if(statuses[j]["order"] == 1)
                                                                  option.attr('selected','selected');
                                                                status_list.append(option);
                                                            }
                                                         })

                          jQuery("#assignee_select").qtip({
                                         content: { prerender: true,
                                                         text: create_user_assign_new_list(),
                                                        title: "Assign user to ticket ..."
                                                   },
                                            show: { delay: 0 },
                                            hide: { fixed: true },
                                        position: { corner: { target: 'topRight', tooltip: 'topLeft' } },
                                           style: { border: { width: 1, color: '#666'} },
                                            });
                        });
});
