phpcli
======

从YII剥离出来 专为命令行或crontab执行php脚本
去掉了YII多余的东西包括复杂的orm


lib/  公共库目录
./classpath.php   init文件
spider/   应用目录  可以改名
---commands/     脚本目录
---component/    组件目录
---config/       配置文件目录
---libraries/    类库
---model/        数据库操作
---runtime/
---cli           可执行文件



使用 
spider/cli  <脚本名>  <方法名>  --参数名=参数值
