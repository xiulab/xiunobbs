ALTER TABLE bbs_forum ADD COLUMN  announcement text NOT NULL;
ALTER TABLE bbs_post ADD COLUMN  message_fmt longtext NOT NULL;
UPDATE bbs_post SET message_fmt=message WHERE 1;
ALTER TABLE bbs_attach ADD COLUMN  isattach tinyint(11) NOT NULL default '0' after isimage;
UPDATE bbs_attach SET isattach=0 where 1;
