# Misc
$c = new DiscourseApi\Client('http://discourse.example.com', 'my_username', 'my_api_key');

print_r($c->createUser('Username', 'Password', 'Email', 'Realname'));
print_r($c->deleteUser(10));
print_r($c->createTopic("TopicTitle", "TopicBody", 0));
print_r($c->like(50));
print_r($c->deletePost(100));
print_r($c->flags('active'));
print_r($c->categories());
print_r($c->category(1));

