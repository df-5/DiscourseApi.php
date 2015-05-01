# Misc
`````
$c = new DiscourseApi\Client('http://discourse.example.com', 'my_username', 'my_api_key');

print_r($c->createUser('Username', 'Password', 'Email', 'Realname'));
print_r($c->deleteUser(10));
print_r($c->createTopic("TopicTitle", "TopicBody", 0));
print_r($c->like(50));
print_r($c->deletePost(100));
print_r($c->flags('active'));
print_r($c->categories());
print_r($c->category(1));
`````

# Functions
`````
 function createTopic($title, $text, $categoryId, $reply = 0)
 function createPost($topicId, $text, $categoryId, $linkedTopic = 0)
 function like($postId)
 function deletePost($postId)
 function deleteTopic($topicId)
 function recoverPost($postId)
 function recoverTopic($topicId)
 function removeUserFromGroup($userId, $groupId)
 function addUserToGroup($userId, $groupId)
 function setUserPrimaryGroup($userId, $groupId)
 function getUserByUsername($username, $stats = false)
 function getUserBadges($username, $group = false)
 function createUser($userName, $password, $email, $fullName = '')
 function deleteUser($uid)
 function anonymizeUser($uid)
 function getAbout()
 function getFlags($type = 'active', $offset = 0)
 function setSetting($key, $value)
 function getCategories()
 function getCategory($slug, $sort = 'latest', $page = 0)
 function getPost($topicId, $postId = 1)
 function getTopic($topicId)
 function searchInForum($query, $context = false)
 function searchInTopic($query, $context = false, $topicId)
 function searchInCategory($query, $context = false, $categoryId)
`````
