! function($) {
    $(function() {
        var button = $(".post-like > span > a");
        button.on("click", function(c) {
            $this=$(this);
            var post_id = $this.data("postid");
            var what_to_do=$this.data("what_to_do");
            $.ajax({
                type: "POST",
                dataType: "json",
                url: like_ajax.url,
                data: {
                    action: "like-post",
                    nonce: like_ajax.nonce,
                    what_to_do: what_to_do,
                    postID: post_id,
                }
            }).done(function(result) {
                console.log(result);
            	var like_link=$(".like_system > a"),
                    dislike_link=$(".dislike_system > a"),
                    like_text=$(".likes_count")
                    dislike_text=$(".dislikes_count");
                like_text.text(result.likes_count);
                dislike_text.text(result.dislikes_count);
                if(result.allow_like=="Yes"){
                    like_link.removeClass("liked");
                }else if(result.allow_like=="No"){
                    like_link.addClass("liked");
                }
                if(result.allow_dislike=="Yes"){
                    dislike_link.removeClass("disliked");
                }else if(result.allow_dislike=="No"){
                    dislike_link.addClass("disliked");
                }
            }), c.preventDefault();
        })
    })
}(jQuery);