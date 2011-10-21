<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "../entities.dtd">
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"/>

	<xsl:template match="module[@name='features' and @action='new']" mode="p-module">
		<form method="post" action="">
			<input type="hidden" name="writemodule" value="FeaturesWriteModule" />
			<input type="hidden" name="id" value="0" />
			<div class="form-group">
				<h2>Добавление теста</h2>
				<div class="form-field">
					<label>Название теста</label>
					<input name="title"/>
				</div>
				<h2>Добавление теста</h2>
				<div class="form-field">
					<label>Пояснение к тесту</label>
					<textarea name="description"></textarea>
				</div>
				<div class="form-field">
					<label>Путь до файла (относительно папки features/)</label>
					<input name="path"/>
				</div>
			</div>
			<div class="form-control">
				<input type="submit" value="Сохранить информацию"/>
			</div>
		</form>
		<script type="text/javascript">
      tinyMCE.init({mode:"textareas"});
		</script>
	</xsl:template>

	<xsl:template match="*" mode="p-feature-groups">
		<table width="100%">
			<tr>
				<td>
					заголовок
				</td>
				<td>
					статус
				</td>
				<td>
					файл теста
				</td>
				<td>
					последний запуск
				</td>
			</tr>
			<xsl:apply-templates select="item" mode="p-feature-group-list-item"/>
		</table>
	</xsl:template>
	
	<xsl:template match="*" mode="p-feature-group-list-item">
		<tr>
				<td colspan="4">
					<h2><xsl:value-of select="@title" /></h2>
				</td>
			</tr>
		<xsl:apply-templates select="features" mode="p-feature-list" />		
	</xsl:template>

	<xsl:template match="*" mode="p-feature-show">
		<ul>
			<xsl:apply-templates select="." mode="p-feature-item" />
		</ul>
	</xsl:template>

	<xsl:template match="*" mode="p-feature-list">
		<xsl:apply-templates select="item" mode="p-feature-list-item" />
	</xsl:template>

	<xsl:template match="*" mode="p-feature-list-item">
		<tr>
			<td>
				<a href="{@path}">
					<xsl:value-of select="@title" />
				</a>
			</td>
			<td>
				<xsl:value-of select="@status" />
			</td>
			<td>
				<xsl:value-of select="@filepath" />
			</td>
			<td>
				<xsl:value-of select="@last_run" />
			</td>
		</tr>

	</xsl:template>

	<xsl:template match="*" mode="p-feature-item">
		<tr>
			<td>
				<a href="{@path}">
					<xsl:value-of select="@title" />
				</a>
			</td>
			<td>
				<xsl:value-of select="@status" />
			</td>
			<td>
				<xsl:value-of select="@filepath" />
			</td>
			<td>
				<xsl:value-of select="@last_run" />
			</td>
		</tr>
		<div>
			<h3>описание теста</h3>
			<xsl:value-of select="@description" />
		</div>
		<div>
			<h3>последний результат тестирования</h3>
			<xsl:value-of select="@last_message" />
		</div>
	</xsl:template>



</xsl:stylesheet>
