
function loadreactions(id_msg, id_emoji, id_member, event) {
	event.preventDefault();

	id_msg = parseInt(id_msg,10);
	id_emoji = parseInt(id_emoji,10);
	id_member = parseInt(id_member,10);

	console.log(id_msg + " - " + id_emoji + " - " + id_member);

	$(document).ready(function() {
		$.ajax({
			url: smf_prepareScriptUrl(smf_scripturl) + 'action=callreactions',
			type: 'post',
			data: {
				msg_id: id_msg,
				emoji_id: id_emoji,
				member_id: id_member
			},
			dataType: 'json',
			success: function(data) {
				$("#counts-" + id_emoji + "-" + id_msg).text(data['no_of_reactions']);
				$("#who_reacted-" + id_emoji + "-" + id_msg).html(data['who_reacted']);
			}
		});
	});
}

//do show who_reacted on hover and hide on hover exit.
$(document).ready(function() {
	$(".who_reacted").hide();

	$(".emojis").hover(function() {
		window.id = this.id;
		window.split_id = id.split("-");
		window.name = split_id[0];
		window.emoji_id = split_id[1];
		window.msg_id = split_id[2];
		$("#who_reacted-" + window.emoji_id + "-" + window.msg_id).show();
	},
	function() {
		$("#who_reacted-" + window.emoji_id + "-" + window.msg_id).hide();
	});
});
