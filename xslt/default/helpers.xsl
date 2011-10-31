<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "entities.dtd">
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"/>
	<xsl:output omit-xml-declaration="yes"/>
	<xsl:output indent="yes"/>

	<xsl:template name="helpers-this-amount">
		<xsl:param name="amount"/>
		<xsl:param name="words"/>
		<xsl:variable name="mod10" select="$amount mod 10"/>
		<xsl:variable name="f5t20" select="$amount>=5 and not($amount>20)"/>
    <xsl:value-of select="$amount"/>
    <xsl:text>&nbsp;</xsl:text>
		<xsl:choose>
			<xsl:when test="not($f5t20) and $mod10=1">
				<xsl:value-of select="substring-before($words,' ')"/>
			</xsl:when>
			<xsl:when test="not($f5t20) and (not($mod10>5) and $mod10>1)">
				<xsl:value-of select="substring-before(substring-after($words,' '),' ')"/>
			</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="substring-after(substring-after($words,' '),' ')"/>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

	<xsl:template name="helpers-abbr-time">
		<xsl:param name="time"/>
		<xsl:if test="$time">
			<abbr class="timeago" title="{$time}">
				<xsl:value-of select="$time"/>
			</abbr>
		</xsl:if>
	</xsl:template>

	<xsl:template match="*" mode="helpers-user-link">
		<a href="{@path}">
			<xsl:value-of select="@nickname"/>
		</a>
	</xsl:template>

	<xsl:template match="*" mode="helpers-user-image">
		<a href="{@path}">
      <img src="{@picture}?{@lastSave}" alt="[{@nickname}]" title="{@nickname}"/>
		</a>
	</xsl:template>

  <xsl:template match="*" mode="h-stylesheet">
    <xsl:variable name="path" select="concat(&prefix;,'static/default/css/',@path,'.css')"/>
    <link href="{$path}" media="screen" rel="stylesheet" type="text/css"/>
  </xsl:template>

  <xsl:template match="*" mode="h-javascript">
    <xsl:variable name="path" select="concat(&prefix;,'static/default/js/',@path,'.js')"/>
    <script src="{$path}" type="text/javascript"></script>
  </xsl:template>

  <xsl:template match="*" mode="h-field-input">
    <xsl:param name="name" select="''"/>
    <xsl:param name="label" select="''"/>
    <div class="form-field">
      <label><xsl:value-of select="$label"/></label>
      <input name="{$name}" value="{@*[name()=$name]}"/>
    </div>
  </xsl:template>


</xsl:stylesheet>
