# 网络爬虫

### 爬虫的架构

1. 预抓取网址集合(TODO list)

2. 已抓取网址集合(visited list)

3. 分析器


### 伪代码
```
todo_list;      # todo集合 (链表)
visited_list;   # visited 集合 (hash，字典，关联数组)

# 添加种子(入口)地址
todo_list.add('http://baidu.com/');

while ( (todo_link = get_next(todo_list)) != null ) {
    html  = get_url_content(todo_link);  # 获取该网址的html内容
    links = extract_links(html);         # 从获取的html中提取链接
    
    # 存储当前页面的数据
    # 表结构：
    # url,content,links(JSON)
    
    # 将获取的链接添加到todo list
    foreach ( links as link ) {
        if ( ! (link in visited_list) ) {   # 该链接没有被访问过
            todo_list.add(link);
        }
    }
    
    # 将todo_link标记为已抓取
    visited_list.add(todo_link);
}


function get_next()
{
    //TODO
}


function get_url_content()
{
    //TODO
}

function extract_links()
{
    //TODO
}

```
