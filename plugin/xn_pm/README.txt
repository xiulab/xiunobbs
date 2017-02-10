【最近联系人列表】
GET /pm-recent_list-{uid}.htm
{code:0, message:[
        {uid: 1, username: "Jack", avatar_url: "view/img/1.png", count: 1},
        {uid: 2, username: "Tom", avatar_url: "view/img/2.png", count: 2},
        {uid: 3, username: "Locy", avatar_url: "view/img/3.png", count: 0}
]}


【两人的聊天记录】
GET /pm-list-{uid1}-{uid2}-{startpmid}.htm
{code:0, message: [ 
        {pmid: 1, uid: 1, username: "Jack", avatar_url: "view/img/1.png", create_date: 1200000000, message: "message 1"}, 
        {pmid: 2, uid: 2, username: "Locy", avatar_url: "view/img/2.png", create_date: 1200000000, message: "message 2"}, 
        {pmid: 3, uid: 1, username: "Jack", avatar_url: "view/img/1.png", create_date: 1200000000, message: "message 3"}, 
        {pmid: 4, uid: 2, username: "Locy", user_av atar_url: "view/img/2.png", create_date: 1200000000, message: "message 4"} 
]}


【新短消息状态】 返回最近联系人列表
GET /pm-new-{uid}.htm
{code:0, message:[
        {uid: 1, username: "Jack", avatar_url: "view/img/1.png", count: 1},
        {uid: 2, username: "Tom", avatar_url: "view/img/2.png", count: 2},
        {uid: 3, username: "Locy", avatar_url: "view/img/3.png", count: 0}
]}


【创建新短消息】
POST /pm-create.htm
touid={123}&message={mesaage}

{code:1, message:"failed reason"}
{code:0, message:{pmid:123, message:"xxx"}}


【搜索两人的聊天记录】
GET /pm-search-{uid1}-{uid2}-{keyword}.htm
{code:0, message: [ 
        {pmid: 1, senduid: 1, username: "Jack", avatar_url: "view/img/1.png", create_date: 1200000000, message: "message 1"}, 
        {pmid: 2, senduid: 2, username: "Locy", avatar_url: "view/img/2.png", create_date: 1200000000, message: "message 2"}, 
        {pmid: 3, senduid: 1, username: "Jack", avatar_url: "view/img/1.png", create_date: 1200000000, message: "message 3"}, 
        {pmid: 4, senduid: 2, username: "Locy", avatar_url: "view/img/2.png", create_date: 1200000000, message: "message 4"} 
]}

【删除新短消息】
POST /pm-delete.htm
pmid={123}

{code:0, message:""}