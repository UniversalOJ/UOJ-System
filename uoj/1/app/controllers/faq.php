<?php
	requireLib('shjs');
	requireLib('mathjax');
	echoUOJPageHeader(UOJLocale::get('help')) 
?>
<article>
	<header>
		<h2 class="page-header">常见问题及其解答(FAQ)</h2>
	</header>
	<section>
		<header>
			<h4><a data-toggle="collapse" href="#collapseOne">1. 什么是<?= UOJConfig::$data['profile']['oj-name-short'] ?></a></h4>
		</header>
		<div id="collapseOne" class="collapse">
			<p>来了？坐，欢迎来到 <?= UOJConfig::$data['profile']['oj-name'] ?>。</p>
			<p><img src="http://tb2.bdstatic.com/tb/editor/images/qpx_n/b37.gif?t=20140803" alt="小熊像超人一样飞" /></p>
			<p>众所周知，信息学的题目一般形式为：给出XXXXX，要你提交一份源代码，输出XXXXX，然后时限若干秒，内存若干兆，数据若干组，每组数据与答案进行比较，不对就不给分。</p>
			<p>看起来挺合理的，但是总是有意外。比如要求输出一个浮点数，与答案接近就满分。于是只好引入Special Judge来判断选手输出的正确性。</p>
			<p>但是还是有意外，比如提交两个程序，一个压缩另一个解压；比如提交答案题只用提交文件；比如给出音乐要求识别乐器，达到90%的正确率就算满分……</p>
			<p>这个时候UOJ出现了，于是<?= UOJConfig::$data['profile']['oj-name-short'] ?>就使用了这套系统。Universal的中文意思是通用，之所以称之为UOJ，因为我们所有题目从编译、运行到评分，都可以由出题人自定义。</p>
			<p>如果你正在为没有地方测奇奇怪怪的题目而苦恼，那么你来对地方了。</p>
			<p>当然了，<?= UOJConfig::$data['profile']['oj-name-short'] ?>对于传统题的评测也做了特别支持。平时做题时我很难容忍的地方就是数据出水了导致暴力得了好多分甚至过了，而出题人却委屈地说，总共才一百分，卡了这个暴力就不能卡另一个暴力，所以暴力过了就过了吧。</p>
			<p>所以我们引入了Extra Tests和Hack机制。每道传统题的数据都分为Tests和Extra Tests，Tests满分100分，如果你通过了所有的Tests，那么就会为你测Extra Tests。如果过了Tests但没过Extra Tests那么倒扣3分变为97分。Extra Tests的来源，一个是这道题没什么人可能会错的边界情况可以放在里面，另一个就是各位平时做题的时候，如果发现错误算法AC了，可以使用hack将其卡掉，<?= UOJConfig::$data['profile']['oj-name-short'] ?>会自动加入Extra Tests并重测。我们无法阻止暴力高分的脚步，但是不让他得满分还是有心里安慰作用的～</p>
			<p><?= UOJConfig::$data['profile']['oj-name-short'] ?>还有比赛功能可以承办比赛，赛制暂时只支持OI赛制。（不过你可以利用现有方案变相实现ACM赛制！）未来将支持更多种多样的赛制甚至自定义赛制。</p>
			<p>目前<?= UOJConfig::$data['profile']['oj-name-short'] ?>刚刚起步，还有很多地方有待完善。想出题、想出比赛、发现BUG、发现槽点都可以联系我们，联系方式见下。</p>
			<p>祝各位在<?= UOJConfig::$data['profile']['oj-name-short'] ?>玩得愉快！（求不虐萌萌哒服务器～求不虐萌萌哒测评机～！）</p>
			<p><img src="http://tb2.bdstatic.com/tb/editor/images/qpx_n/b54.gif?t=20140803" alt="小熊抱抱" /></p>
		</div>
	</section>
	<section>
		<header>
			<h4><a data-toggle="collapse" href="#collapseTwo">2. 注册后怎么上传头像</a></h4>
		</header>
		<div id="collapseTwo" class="collapse">
			<p><?= UOJConfig::$data['profile']['oj-name-short'] ?>不提供头像存储服务。每到一个网站都要上传一个头像挺烦的对不对？我们支持Gravatar，请使用Gravatar吧！Gravatar是一个全球的头像存储服务，你的头像将会与你的电子邮箱绑定。在各大网站比如各种Wordpress还有各种OJ比如Vijos、Contest Hunter上，只要你电子邮箱填对了，那么你的头像也就立即能显示了！</p>
			<p>快使用Gravatar吧！ Gravatar地址：<a href="https://cn.gravatar.com/">https://cn.gravatar.com/</a>。进去后注册个帐号然后与邮箱绑定并上传头像，就ok啦！</p>
		</div>
	</section>
	<section>
		<header>
			<h4><a data-toggle="collapse" href="#collapseThree">3. <?= UOJConfig::$data['profile']['oj-name-short'] ?>的测评环境？</a></h4>
		</header>
		<div id="collapseThree" class="collapse">
			<p>默认的测评环境是 Ubuntu Linux 14.04 LTS x64。</p>
			<p>C++的编译器是 g++ 4.8.4，编译命令：<code>g++ code.cpp -o code -lm -O2 -DONLINE_JUDGE</code>。如果选择C++11会在编译命令后面添加<code>-std=c++11</code>。</p>
			<p>C的编译器是 gcc 4.8.4，编译命令：<code>gcc code.c -o code -lm -O2 -DONLINE_JUDGE</code>。</p>
			<p>Pascal的编译器是 fpc 2.6.2，编译命令：<code>fpc code.pas -O2</code>。</p>
			<p>Java7的JDK版本是 jdk-7u76，编译命令：<code>javac code.java</code>。</p>
			<p>Java8的JDK版本是 jdk-8u31，编译命令：<code>javac code.java</code>。</p>
			<p>Python会先编译为优化过的字节码<samp>.pyo</samp>文件。支持的Python版本分别为Python 2.7和3.4。</p>
		</div>
	</section>
	<section>
		<header>
			<h4><a data-toggle="collapse" href="#collapseFour">4. 各种评测状态的鸟语是什么意思？</a></h4>
		</header>
		<div id="collapseFour" class="collapse">
			<ul>
				<li>Accepted: 答案正确。恭喜大佬，您通过了这道题。</li>
				<li>Wrong Answer: 答案错误。仅仅通过样例数据的测试并不一定是正确答案，一定还有你没想到的地方。</li>
				<li>Runtime Error: 运行时错误。像非法的内存访问，数组越界，指针漂移，调用禁用的系统函数都可能出现这类问题，请点击评测详情获得输出。</li>
				<li>Time Limit Exceeded: 时间超限。请检查程序是否有死循环，或者应该有更快的计算方法。</li>
				<li>Memory Limit Exceeded: 内存超限。数据可能需要压缩，或者您数组开太大了，请检查是否有内存泄露。</li>
				<li>Output Limit Exceeded: 输出超限。你的输出居然比正确答案长了两倍！</li>
				<li>Dangerous Syscalls: 危险系统调用，你是不是带了文件，或者使用了某些有意思的system函数？</li>
				<li>Judgement Failed: 评测失败。可能是评测机抽风了，也可能是服务器正在睡觉；反正不一定是你的锅啦！</li>
				<li>No Comment: 没有详情。评测机对您的程序无话可说，那么我们也不知道到底发生了什么...</li>
			</ul>
		</div>
	</section>
	<section>
		<header>
			<h4><a data-toggle="collapse" href="#collapseFive">5. 递归 10<sup>7</sup> 层怎么没爆栈啊</a></h4>
		</header>
		<div id="collapseFive" class="collapse">
			<p>没错就是这样！除非是特殊情况，<?= UOJConfig::$data['profile']['oj-name-short'] ?>测评程序时的栈大小与该题的空间限制是相等的！</p>
		</div>
	</section>
	<section>
		<header>
			<h4><a data-toggle="collapse" href="#collapseSix">6. 我在本地/某某OJ上AC了，但在<?= UOJConfig::$data['profile']['oj-name-short'] ?>却过不了...这咋办？</a></h4>
		</header>
		<div id="collapseSix" class="collapse">
			<p>对于这类问题，我们在这里简单列一下可能原因：</p>
			<ul>
				<li>Linux中换行符是'\n'而windows中是'\r\n'（多一个字符）。有些数据在Windows下生成，而<?= UOJConfig::$data['profile']['oj-name-short'] ?>评测环境为Linux系统。这种情况在字符串输入中非常常见。</li>
				<li>评测系统建立在Linux下，可能由于使用了Linux的保留字而出现CE，但在Windows下正常。</li>
				<li>Linux对内存的访问控制更为严格，因此在Windows上可能正常运行的无效指针或数组下标访问越界，在评测系统上无法运行。</li>
				<li>严重的内存泄露的问题很可能会引起系统的保护模块杀死你的进程。因此，凡是使用malloc(或calloc,realloc,new)分配而得的内存空间，请使用free(或delete)完全释放。</li>
				<li>当然数据可能真的有问题。但是如果不止一个人通过了这道题，那最好不要怀疑是数据的锅。反之，可以立即联系我们上报！</li>
			</ul>
		</div>
	</section>
	<section>
		<header>
			<h4><a data-toggle="collapse" href="#collapseSeven">7. 博客使用指南</a></h4>
		</header>
		<div id="collapseSeven" class="collapse">
			<p><?= UOJConfig::$data['profile']['oj-name-short'] ?>博客使用的是Markdown。（好吧……好简陋的……好多功能还没写……）</p>
			<p>（喂喂喂我们是OJ好吗……要那么完善的博客功能干啥呢……？）</p>
			<p>其实我觉得Markdown不用教！一学就会！</p>
			<p>（完蛋了……<?= UOJConfig::$data['profile']['oj-name-short'] ?>好像没有Markdown的语法高亮……= =……）</p>
			<p>我就只介绍最基本的功能好了。其它的自己探索吧～比如<a href="http://wowubuntu.com/markdown/">这里</a>。</p>
			<!-- readmore -->
			<p><code>**强调**</code> = <strong>强调</strong></p>
			<hr /><p><code>*强调*</code> = <em>强调</em></p>
			<hr /><p><code>[<?= UOJConfig::$data['profile']['oj-name-short'] ?>](http://<?= UOJConfig::$data['web']['main']['host'] ?>)</code> = <a href="http://<?= UOJConfig::$data['web']['main']['host'] ?>"><?= UOJConfig::$data['profile']['oj-name-short'] ?></a></p>
			<hr /><p><code>http://<?= UOJConfig::$data['web']['main']['host'] ?></code> = <a href="http://<?= UOJConfig::$data['web']['main']['host'] ?>">http://<?= UOJConfig::$data['web']['main']['host'] ?></a></p>
			<hr /><p><code>![这个文字在图挂了的时候会显示](http://<?= UOJConfig::$data['web']['main']['host'] ?>/pictures/UOJ.ico)</code> =
			<img src="http://<?= UOJConfig::$data['web']['main']['host'] ?>/pictures/UOJ.ico" alt="这个文字在图挂了的时候会显示" /></p>
			<hr /><p><code>`rm orz`</code> = <code>rm orz</code></p>
			<hr /><p><code>数学公式萌萌哒$(a + b)^2$萌萌哒</code> = 数学公式萌萌哒$(a + b)^2$萌萌哒</p>
			<hr /><p><code>&lt;!-- readmore --&gt;</code> = 在外面看这篇博客时会到此为止然后显示一个“阅读更多”字样</p>
			<hr /><p>来个更大的例子：</p>
			<pre>
			```c++
			#include &lt;iostream&gt;
			```

			```c
			#include &lt;stdio.h&gt;
			```

			```pascal
			begin
			```

			```python
			print '<?= UOJConfig::$data['profile']['oj-name-short'] ?>'
			```

			\begin{equation}
			\frac{-b + \sqrt{b^2 - 4ac}}{2a}
			\end{equation}

			#一级标题
			##二级标题
			###三级标题
			####四级标题
			</pre>
			<p>会转换为：</p>
			<pre><code class="sh_cpp">#include &lt;iostream&gt;</code></pre>
			<pre><code class="sh_c">#include &lt;stdio.h&gt;</code></pre>
			<pre><code class="sh_pascal">begin</code></pre>
			<pre><code class="sh_python">print '<?= UOJConfig::$data['profile']['oj-name-short'] ?>'</code></pre>
			<p>\begin{equation}
			\frac{-b + \sqrt{b^2 - 4ac}}{2a}
			\end{equation}</p>
			<h1>一级标题</h1>
			<h2>二级标题</h2>
			<h3>三级标题</h3>
			<h4>四级标题</h4>
			<hr /><p>还有一个很重要的事情，就是你很容易以为<?= UOJConfig::$data['profile']['oj-name-short'] ?>在吃换行……</p>
			<p>那是因为跟LaTeX一样，你需要一个空行来分段。你可以粗略地认为两个换行会被替换成一换行。（当然不完全是这样，空行是用来分段的，段落还有间距啊行首空两格啊之类的属性）</p>
			<p>唔……就介绍到这里吧。想要更详细的介绍上网搜搜吧～</p>
			<p>（评论区是不可以用任何HTML滴～但是数学公式还是没问题滴）</p>
		</div>
	</section>
	<section>
		<header>
			<h4><a data-toggle="collapse" href="#collapseEight">8. 交互式类型的题怎么本地测试</a></h4>
		</header>
		<div id="collapseEight" class="collapse">
			<p>唔……好问题。交互式的题一般给了一个头文件要你include进来，以及一个实现接口的源文件grader。好像大家对多个源文件一起编译还不太熟悉。</p>
			<p>对于C++：<code>g++ -o code grader.cpp code.cpp</code></p>
			<p>对于C语言：<code>gcc -o code grader.c code.c</code></p>
			<p>如果你是悲催的电脑盲，实在不会折腾没关系！你可以把grader的文件内容完整地粘贴到你的code的include语句之后，就可以了！</p>
			<p>什么你是萌萌哒Pascal选手？一般来说都会给个grader，你需要写一个Pascal单元。这个grader会使用你的单元。所以你只需要把源文件取名为单元名 + <code>.pas</code>，然后：</p>
			<p>对于Pascal语言：<code>fpc grader.pas</code></p>
			<p>就可以啦！</p>
		</div>
	</section>
	<section>
		<header>
			<h4><a data-toggle="collapse" href="#collapseNine">9. 联系方式</a></h4>
		</header>
		<div id="collapseNine" class="collapse">
			<p>如果你想出题、想办比赛、发现了BUG或者对网站有什么建议，可以通过下面的方式联系我们：</p>
			<ul>
				<li>私信联系<?= UOJConfig::$data['profile']['administrator'] ?>。</li>
				<li>邮件联系<?= UOJConfig::$data['profile']['admin-email'] ?>。</li>
				<?php if (UOJConfig::$data['profile']['QQ-group']!=''): ?>
				<li>你也可以进QQ群水水，群号是<?= UOJConfig::$data['profile']['QQ-group'] ?>。</li>
				<?php endif ?>
			</ul>
		</div>
	</section>
</article>

<?php echoUOJPageFooter() ?>
