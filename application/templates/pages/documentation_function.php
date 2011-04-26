<!-- begin breadcrumb -->
<div id="breadcrumb">
<div class="inner">
<a href="<?php echo APP_URI_BASE; ?>home">Home</a> &gt; <a href="<?php echo APP_URI_BASE; ?>documentation/">Documentation</a> &gt; Function
</div>
</div>
<!-- end breadcrumb -->
<div class="clear"></div>

<!-- begin onecolumn -->
<div id="onecolumn" class="inner"> 
<h1 class="first_heading">Function</h1>	

<p>
InfoPotato allows user to load any User-defined Functions to facilate the manager's with tasks. Each function file is simply a PHP function in a particular category.
</p> 

<p>
Unlike most other components in InfoPotato, User-defined Functions are not written in an Object Oriented format. They are simple, procedural functions. Each function performs one specific task, with no dependence on other functions.
</p>

<p>
Function is loaded using the load_function() in manager, and can only be used by managers.
</p>
 
<div class="syntax"><pre>
<span class="c1">// Load a system provided function to help form process</span> 
<span class="nv">$this</span><span class="o">-&gt;</span><span class="na">load_function</span><span class="p">(</span><span class="s1">&#39;SYS&#39;</span><span class="p">,</span> <span class="s1">&#39;htmlawed/htmlawed_function&#39;</span><span class="p">);</span> 
</pre></div>

<p>
Then the functions in download_script.php are available to be called.
</p>

<h2>Create your own function</h2>

<p>
A function may be defined using syntax such as the following:
</p>

<div class="syntax"><pre><span class="cp">&lt;?php</span> 
<span class="k">function</span> <span class="nf">foo_function</span><span class="p">(</span><span class="nv">$arg_1</span><span class="p">,</span> <span class="nv">$arg_2</span><span class="p">,</span> <span class="cm">/* ..., */</span> <span class="nv">$arg_n</span><span class="p">)</span> <span class="p">{</span> 
    <span class="k">echo</span> <span class="s2">&quot;Example function.</span><span class="se">\n</span><span class="s2">&quot;</span><span class="p">;</span> 
    <span class="k">return</span> <span class="nv">$retval</span><span class="p">;</span> 
<span class="p">}</span> 
</pre></div> 

<div class="tipbox">
All the user-defined functions should be named like xxx_function
</div>

</div> 
<!-- end onecolumn -->
