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

</xsl:stylesheet>
