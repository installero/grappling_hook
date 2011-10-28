<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "entities.dtd">
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"/>
	<xsl:output omit-xml-declaration="yes"/>

	<xsl:template match="module">
    <xsl:apply-templates select="conditions/item[not(@mode='paging')]" mode="p-misc-condition"/>
    <div class="m-{@name}-{@action} module">
      <xsl:apply-templates select="." mode="p-module"/>
    </div>
    <xsl:apply-templates select="conditions/item[@mode='paging']" mode="p-misc-condition"/>
	</xsl:template>
 
  <xsl:template match="module[@name='features' and @action='list']" mode="p-module">
    <xsl:param name="title" select="features/@title"/>
    <xsl:param name="amount" select="features/@count"/>
    <h2><xsl:value-of select="$title" /> (<xsl:value-of select="$amount"/>)</h2>
    <xsl:apply-templates select="groups" mode="p-feature-groups"/>
  </xsl:template>
  
  <xsl:template match="module[@name='features' and @action='show']" mode="p-module">
    <xsl:apply-templates select="feature" mode="p-feature-show"/>
  </xsl:template>

</xsl:stylesheet>
