phpcli
======

从YII剥离出来 专为命令行或crontab执行php脚本
去掉了YII多余的东西包括复杂的orm


<p>lib/  公共库目录<br>
./classpath.php   init文件<br>
spider/   应用目录  可以改名<br>
---commands/     脚本目录<br>
---component/    组件目录<br>
---config/       配置文件目录<br>
---libraries/    类库<br>
---model/        数据库操作<br>
---runtime/ <br>
---cli           可执行文件<br>
</p>
-----

使用 <br>
spider/cli  <脚本名>  <方法名>  --参数名=参数值
