##IOC服务容器

container组件用于IOC服务容器管理

###安装组件
使用 composer 命令进行安装或下载源代码使用。

    composer require willphp/container

> WillPHP框架已经内置此组件，无需再安装。

###注入容器

定义一个测试类并绑定到容器：

	class Test{
	    public function show(){return 'willphp';}
	}
	\willphp\container\Container::bind('test', function () {
	        return new Test();
	});
	\willphp\container\Container::make('test')->show();

###单例注入

使用instance：

	Container::instance('test', new Test());
	Container::make('test')->show();

或使用single：

	Container::single('test',function () {
	        return new Test();
	});
	Container::make('test')->show();

###调用方法

	echo Container::callMethod(Test::class, 'show');

###调用函数

	$res = Container::callFunction(function (Test $test) {
	     return $test->show();
	});
 