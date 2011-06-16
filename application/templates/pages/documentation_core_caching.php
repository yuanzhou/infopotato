<!-- begin onecolumn -->
<div id="onecolumn" class="inner"> 

<!-- begin breadcrumb -->
<div id="breadcrumb">
<!-- PRINT: start -->
<h1 class="first_heading">Caching</h1>	
<!-- PRINT: stop -->
<a href="<?php echo APP_URI_BASE; ?>home">Home</a> &gt; <a href="<?php echo APP_URI_BASE; ?>documentation/">Documentation</a> &gt; <a href="<?php echo APP_URI_BASE; ?>documentation/core/">Core Topics</a> &gt; Caching
</div>
<!-- end breadcrumb -->

<a href="http://www.phpwact.org/php/caching">http://www.phpwact.org/php/caching</a>

<a href="<?php echo APP_URI_BASE; ?>print" class="print">Print</a>

<!-- PRINT: start -->
<p>
Caching is the practice of trading space (RAM or disk) for extra speed. Data is processed and the result saved so that it won’t have to be reprocessed later. This works best with data that is time consuming to process but doesn’t change very often.
</p>

<p>
There are a number of different caching mechanisms (which perform logically different tasks) which turn up in PHP applications and should be understood to avoid confusion.
</p> 
 
<h2>PHP Script Caching</h2>

<p>
The core of PHP is the Zend Engine which parses PHP code and translates it into a byte code, then interprets the byte code. In PHP the source code file is reparsed for every request made to the server, even if no changes have been made since the last time it was parsed. Script Caching saves the byte code to a file (known as compiling), so that the source code does not have to be reparsed if it hasn’t changed. Parsing can take a substantial amount of time on each request. For many scripts, the time required to parse the PHP code into byte code will take longer than the time need to interpret it.
</p>

<p>
When a site visitor asks the web server for a PHP page, the web server starts a PHP engine to run that script. The engine converts programmer instructions into simpler instructions the computer can understand. This conversion process, or compilation, takes time. When the compilation is done, the engine runs the PHP program, which builds a web page and returns it to the site visitor.
</p>

<p>
By default, the PHP engine re-compiles the requested PHP scripts every time a visitor loads a web page for the site. Because the PHP application program changes rarely (only when you install a new version), re-compiling it over and over again is redundant. Instead, when a PHP accelerator script cache is installed, the PHP engine compiles the application just once and saves the results into the script cache. The next time the program is needed, the engine re-uses the compiled script in the cache. This saves time, speeding up the web site.
</p>

<p>
There are a number of popular Script Caching engines available which act as "add ons" to the base PHP installation;
</p>

<ul>
<li>Zend Accelerator</li>
<li>Alternative PHP Cache</li>
<li>XCache</li>
<li>eAccelerator</li>
</ul>

<p>
Script caching can be implemented in an manner independent from your code (i.e. there’s no need to make modifications)
</p>

<h2>Template Caching</h2>

<p>
Template caching occurs when you’re using a template engine that has invented it’s own syntax in some form (such as Smarty).
</p>

<p>
Given that a template for a page will only change infrequently (when a modification is made to the look and feel of that page), it’s important not to “interpret” the template at runtime, given the overhead of this process.
</p>
 
<p>
More advanced template engines will compile a template into native PHP code and cache it as a file for re-use until the original template changes.
</p>

<p>
Such a caching mechanism will typically be part of the template engine and implemented in a form so that users of the template engine need not be concerned about it’s impact on their application design.
</p>

<h2>Output Caching</h2>

<p>
PHP provides an Output Control Functionality which allows your scripts to determine when output is actually “transmitted” to a client browser. This mechanism allows *compiled HTML* to be cached for re-use.
</p>

<p>
For most web based applications, dynamic content will change on an intermitent basis, a single page being typically made up of multiple “sections” of dynamic content, each section subject to different factors, determining how frequently it changes.
</p>

<p>
The process of compiling the HTML for that page will involve different operations, such as fetching data from a database, for a list of news items, while using PHP‘s date() function to display the current time.
</p>

<p>
It is possible to cache the compiled HTML sections, meaning the compiled versions are used for future requests to that page, each section being recompiled when it changes.
</p>

<p>
The advantage of compiling output is “expensive” operations, such as fetching data from a database, can be restricted only to instances where the output has really changed.
</p>

<p>
Implementing output caching has a significant impact of the design on an application (hence most PHP applications today don’t use it). It requires hooking up (the operations that insert / update content) to the output caching mechanism. An Observer makes a good choice to implement such functionality, allowing routines which flush the cache to observe the routines which modify content. In practice, it also requires consistent use of a framework to successfully implement.
</p>

<h2>Database Query Caching</h2>

<p>
Caching the results of database queries can reduce the number of requests made to a database server and increase the scalabity of your applications.
</p>

<h2>Client Side HTTP Caching</h2>

<p>
The HTTP protocol has an in built caching mechanism (see <a href="http://www.mnot.net/cache_docs/" class="external_link">Caching Tutorial for Web Authors and Webmasters</a>) which allows a web browser to negotiate with a web server in determining whether it should use a locally cached copy of the page (stored on the client file system) or fetch a fresh copy from the server.
</p>

<p>
Although web servers such as Apache already handle such negotiations, it is based on the modification time of web pages on the server. For server side platforms like PHP, this presents a problem, as a script may not change but the output it delivers does.
</p>

<p>
Scripts, therefore, need deal with HTTP caching headers correctly. A simple but effective approach, for most situations, is to work only with the HTTP 1.0 headers “Last-Modified” (response header) and “If-Modified-Since” (request header), as well as the “304 Not Modified” status code.
</p>

<p>
As HTTP cache headers work on a page by page basis, the script handling these headers needs to know the age of the content it’s rendering. In practice this form of caching works best in conjunction with output caching (above).
</p>

<p>
The advantage of using HTTP caching is to save bandwidth as well as make your pages appear faster to the user.
</p>
<!-- PRINT: stop -->

<?php echo isset($pager) ? $pager : ''; ?>

</div> 
<!-- end onecolumn -->