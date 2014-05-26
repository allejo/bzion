<?php

class ModelTest extends TestCase
{
    public function testEscape()
    {
        $this->assertEquals("some text", Model::escape("some text"));
        $this->assertEquals("υτφ8 τεχτ", Model::escape("υτφ8 τεχτ"));

        $this->assertEquals(
            "&lt;script&gt;alert(&#039;i will h4x0r u&#039;)&lt;/script&gt;",
            Model::escape("<script>alert('i will h4x0r u')</script>")
        );

        $this->assertEquals(
            "&quot;hack&quot;=-1",
            Model::escape('"hack"=-1')
        );
    }
}
