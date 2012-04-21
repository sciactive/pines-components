Introduction
============

fancyBox : http://fancyapps.com/fancybox/

The fancyBox inline module can take any options listed on fancyBox's site.
However, for the "content" option, you must use "option_content" instead.

The content of the inline module will be used to create the fancyBox. It looks
for elements with the "fancybox-elem" class.



Examples
========

To use fancyBox for a single sample image in an inline module, insert something
like this into a page's content:

	[com_fancybox/fancybox]
	<a class="fancybox-elem" href="/pines/media/fancybox_sample/Flower1.jpg">
		<img src="/pines/media/fancybox_sample/Flower1_t.jpg" alt="" />
	</a>
	[/com_fancybox/fancybox]


To use fancyBox for the whole sample gallery, something like this:

	[com_fancybox/fancybox]
	<a class="fancybox-elem" href="/pines/media/fancybox_sample/Flower1.jpg" rel="gallery1">
		<img src="/pines/media/fancybox_sample/Flower1_t.jpg" alt="" />
	</a>
	<a class="fancybox-elem" title="Isn't it green?" href="/pines/media/fancybox_sample/Greenest.jpg" rel="gallery1">
		<img src="/pines/media/fancybox_sample/Greenest_t.jpg" alt="" />
	</a>
	<a class="fancybox-elem" title="No ducks were harmed in the making of this image." href="/pines/media/fancybox_sample/PatitoVerde.jpg" rel="gallery1">
		<img src="/pines/media/fancybox_sample/PatitoVerde_t.jpg" alt="" />
	</a>
	<a class="fancybox-elem" href="/pines/media/fancybox_sample/Windmill.jpg" rel="gallery1">
		<img src="/pines/media/fancybox_sample/Windmill_t.jpg" alt="" />
	</a>
	[/com_fancybox/fancybox]


Or for a much fancier look, you can custom style fancyBox like this (requires
com_istyle):

	[com_istyle/style]
	.fancy_pretty a, .fancy_pretty a:hover {
		text-decoration: none;
	}
	.fancy_pretty img {
		background: none repeat scroll 0 0 white;
		border: 1px solid #BBBBBB;
		margin: 0 7px 7px 0;
		padding: 5px;
	}
	.fancy_custom .fancybox-skin {
		border-radius: 0;
	}
	.fancy_custom .fancybox-title .child {
		border-radius: 0;
		padding: 2px 10px;
	}
	[/com_istyle/style]
	[com_fancybox/fancybox class="fancy_pretty" wrapCSS="fancy_custom" padding="0" closeBtn="false" openEffect="elastic" closeEffect="fade" nextEffect="fade" prevEffect="fade" openOpacity="true"]
	<a class="fancybox-elem" href="/pines/media/fancybox_sample/Flower1.jpg" rel="gallery1">
		<img src="/pines/media/fancybox_sample/Flower1_t.jpg" alt="" />
	</a>
	<a class="fancybox-elem" title="Isn't it green?" href="/pines/media/fancybox_sample/Greenest.jpg" rel="gallery1">
		<img src="/pines/media/fancybox_sample/Greenest_t.jpg" alt="" />
	</a>
	<a class="fancybox-elem" title="No ducks were harmed in the making of this image." href="/pines/media/fancybox_sample/PatitoVerde.jpg" rel="gallery1">
		<img src="/pines/media/fancybox_sample/PatitoVerde_t.jpg" alt="" />
	</a>
	<a class="fancybox-elem" href="/pines/media/fancybox_sample/Windmill.jpg" rel="gallery1">
		<img src="/pines/media/fancybox_sample/Windmill_t.jpg" alt="" />
	</a>
	[/com_fancybox/fancybox]


And finally, to show a Youtube video in fancyBox, something like this:

	[com_fancybox/fancybox helpers='{"media":{}}']
	<a class="fancybox-elem" href="http://www.youtube.com/watch?v=L9szn1QQfas">
		<img src="http://i4.ytimg.com/vi/L9szn1QQfas/default.jpg" alt="" />
	</a>
	[/com_fancybox/fancybox]



Credit
======

All of these images are Copyright Hunter Perrin. They are licensed under a
Creative Commons Attribution-ShareAlike 3.0 Unported License.
http://creativecommons.org/licenses/by-sa/3.0/

I don't care if you attribute me, just share alike.
(Originals: https://sites.google.com/site/hperrin/myphotography)
