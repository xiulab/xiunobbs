xn_tag_cate
cateid
catename
tagid

xn_tag_name
tagid
name

xn_tag
tagid
tid

SELECT tid FROM xn_tag WHERE tagid='$tagid1' AND tid IN(
	SELECT tid FROM xn_tag WHERE tagid='$tagid2' AND tid IN (
		SELECT tid FROM xn_tag WHERE tagid='$tagid3'
	)
) 

