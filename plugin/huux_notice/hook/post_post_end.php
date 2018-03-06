<?php exit;

	$thread['subject'] = notice_substr($thread['subject'], 20);

	// 回复
	$notice_message = '<div class="comment-info"><a class="mr-1 text-grey" href="'.url("thread-$thread[tid]").'#'.$pid.'">'.lang('notice_lang_comment').'</a>'.lang('notice_message_replytoyou').'<a href="'.url("thread-$thread[tid]").'">《'.$thread['subject'].'》</a></div><div class="single-comment"><a href="'.url("thread-$thread[tid]").'#'.$pid.'">'.notice_substr($message, 40, FALSE).'</a></div>';
	$recvuid = $thread['uid'];

	// hook notice_post_post_end_reply.php

	notice_send($uid, $recvuid, $notice_message, 2);

	// 引用
	if(!empty($quotepid) && $quotepid > 0) {

		// hook notice_post_post_end_quote.php
		$notice_quote_message = '<div class="comment-info"><a class="mr-1 text-grey" href="'.url("thread-$thread[tid]").'#'.$pid.'">'.lang('notice_lang_reply').'</a>'.lang('notice_message_replytoyou_at').'<a href="'.url("thread-$thread[tid]").'">《'.$thread['subject'].'》</a>'.lang('notice_message_replytoyou_for').'</div><div class="quote-comment">'.notice_substr($quotepost['message'], 40, FALSE).'</div><div class="reply-comment"><a href="'.url("thread-$thread[tid]").'#'.$pid.'">'.notice_substr($message, 40, FALSE).'</a></div>';



		notice_send($uid, $quotepost['uid'], $notice_quote_message, 2);	
	}

?>