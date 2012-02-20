<?php
/**
 * Prints extensive example content.
 *
 * @package Pines
 * @subpackage com_example
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = htmlspecialchars($this->position);
$this->note = 'This is example content.';
?>
<p>
	Curabitur imperdiet dictum leo, eu aliquam lorem vestibulum et. Proin ornare
	nibh sit amet urna lobortis pulvinar. Aenean dui tortor, interdum non
	placerat in, rutrum sed quam. Ut urna nunc, laoreet sed mollis sed, pharetra
	quis magna. Etiam placerat eleifend dapibus. Morbi aliquam lobortis
	interdum. Pellentesque dictum, felis quis luctus bibendum, turpis nunc
	feugiat sem, sed imperdiet diam quam eget sapien. Fusce scelerisque suscipit
	turpis porta viverra. Nunc nec nisi eget lorem fermentum gravida sed vitae
	risus. Nulla erat leo, ornare imperdiet convallis id, malesuada ut tellus.
	Aliquam vitae risus id elit pulvinar elementum. Fusce semper pulvinar dui
	non semper. Class aptent taciti sociosqu ad litora torquent per conubia
	nostra, per inceptos himenaeos. Sed quis quam eu justo lobortis tempor.
	Donec hendrerit risus quis leo pharetra aliquet. Fusce condimentum lobortis
	adipiscing. Duis quam lorem, semper et tempor eu, tristique sed ligula. Nam
	malesuada placerat dui, eget tempus erat dignissim id.
</p>
<ul class="thumbnails">
	<li class="span4">
		<a class="thumbnail" href="#">
			<img alt="" src="http://placehold.it/360x268" />
		</a>
	</li>
	<li class="span2">
		<a class="thumbnail" href="#">
			<img alt="" src="http://placehold.it/160x120" />
		</a>
	</li>
	<li class="span2">
		<a class="thumbnail" href="#">
			<img alt="" src="http://placehold.it/160x120" />
		</a>
	</li>
	<li class="span2">
		<a class="thumbnail" href="#">
			<img alt="" src="http://placehold.it/160x120" />
		</a>
	</li>
	<li class="span2">
		<a class="thumbnail" href="#">
			<img alt="" src="http://placehold.it/160x120" />
		</a>
	</li>
	<li class="span2">
		<a class="thumbnail" href="#">
			<img alt="" src="http://placehold.it/160x120" />
		</a>
	</li>
</ul>
<div class="row-fluid">
	<div class="span6">
		<h3>Fusce Sit Amet</h3>
		<p>
			Phasellus eu lectus massa. Vestibulum eu sem risus. Proin lacus
			metus, consequat sodales imperdiet vitae, accumsan in diam.
			Pellentesque tempus, nulla a rutrum pretium, augue urna fringilla
			lacus, in pulvinar lectus enim ut enim. Nam venenatis congue erat,
			sed placerat nibh elementum eu.
		</p>
	</div>
	<div class="span3">
		<h3>In Varius Risus</h3>
		<div style="padding-top: 2em;">
			<button class="btn btn-large">Morbi Est Tortor</button>
		</div>
	</div>
	<div class="span3">
		<h3><a href="#">Duis Convallis</a></h3>
		<p>
			Duis <a href="#">convallis</a> est ut augue fringilla non elementum risus cursus.
		</p>
		<ul>
			<li>Aenean.</li>
			<li>Nulla.</li>
			<li>Etiam.</li>
		</ul>
	</div>
</div>
<div class="row-fluid">
	<div class="span4">
		<h3>Typography</h3>
		<p>
			<strong>strong</strong> <em>em</em> <abbr title="abbreviation">abbr</abbr> <b>b tag</b> <i>i tag</i> <code>code</code>
		</p>
		<address>
			<strong>Address, Inc</strong><br />
			123 Fake St<br>
			San Diego, CA 92123<br />
			<abbr title="Phone">P:</abbr> (800) 555-1234
		</address>
	</div>
	<div class="span4">
		<h3>Definition List</h3>
		<dl>
			<dt>Praesent</dt>
			<dd>Sed volutpat tristique neque, id adipiscing nunc sollicitudin et.</dd>
			<dt>Varius</dt>
			<dd>Sed vitae enim massa, in accumsan leo.</dd>
			<dt>Euismod</dt>
			<dd>Aliquam varius fermentum tellus, vel rutrum nisi sagittis ut.</dd>
		</dl>
	</div>
	<div class="span4">
		<h1>h1. Heading</h1>
		<h2>h2. Heading</h2>
		<h3>h3. Heading</h3>
		<h4>h4. Heading</h4>
		<h5>h5. Heading</h5>
		<h6>h6. Heading</h6>
	</div>
</div>
<div class="row-fluid">
	<div class="span6">
		<h3>Blockquote</h3>
		<p>Blockquote with a cite:</p>
		<blockquote>
			Ut id lectus nec mauris varius laoreet at sed ligula. Mauris tempus
			varius nibh at placerat.
			<small>Generic McPerson in <cite title="The pre tag example.">The Next Paragraph</cite></small>
		</blockquote>
	</div>
	<div class="span6">
		<h3>Pre Tag</h3>
		<pre>Ut id lectus nec mauris varius laoreet at sed ligula. Mauris tempus varius nibh at placerat. Ut mollis, dui quis facilisis luctus, nibh diam dignissim eros, vel sagittis felis lorem a velit.</pre>
	</div>
</div>
<div class="row-fluid">
	<div class="span6">
		<h3>Default Table</h3>
		<table>
			<thead>
				<tr>
					<th>Author</th>
					<th>Book</th>
					<th>ISBN-13</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>Douglas Adams</td>
					<td>The Hitchhiker's Guide to the Galaxy</td>
					<td>9780345391803</td>
				</tr>
				<tr>
					<td>Lois Lowry</td>
					<td>The Giver</td>
					<td>9780440237686</td>
				</tr>
				<tr>
					<td>Suzanne Collins</td>
					<td>The Hunger Games</td>
					<td>9780439023528</td>
				</tr>
			</tbody>
		</table>
	</div>
	<div class="span6">
		<h3>Styled Table</h3>
		<table class="table">
			<thead>
				<tr>
					<th>Author</th>
					<th>Book</th>
					<th>ISBN-13</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>Douglas Adams</td>
					<td>The Hitchhiker's Guide to the Galaxy</td>
					<td>9780345391803</td>
				</tr>
				<tr>
					<td>Lois Lowry</td>
					<td>The Giver</td>
					<td>9780440237686</td>
				</tr>
				<tr>
					<td>Suzanne Collins</td>
					<td>The Hunger Games</td>
					<td>9780439023528</td>
				</tr>
			</tbody>
		</table>
	</div>
</div>