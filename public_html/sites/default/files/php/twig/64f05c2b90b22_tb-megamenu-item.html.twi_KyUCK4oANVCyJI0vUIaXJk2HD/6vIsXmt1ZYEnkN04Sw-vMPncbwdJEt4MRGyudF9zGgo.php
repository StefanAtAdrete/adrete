<?php

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Extension\SandboxExtension;
use Twig\Markup;
use Twig\Sandbox\SecurityError;
use Twig\Sandbox\SecurityNotAllowedTagError;
use Twig\Sandbox\SecurityNotAllowedFilterError;
use Twig\Sandbox\SecurityNotAllowedFunctionError;
use Twig\Source;
use Twig\Template;

/* modules/contrib/tb_megamenu/templates/tb-megamenu-item.html.twig */
class __TwigTemplate_453f7d892836088988c61a7f4ed93732 extends Template
{
    private $source;
    private $macros = [];

    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->parent = false;

        $this->blocks = [
        ];
        $this->sandbox = $this->env->getExtension('\Twig\Extension\SandboxExtension');
        $this->checkSecurity();
    }

    protected function doDisplay(array $context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 1
        $context["linkAttributes"] = (($__internal_compile_0 = ($context["link"] ?? null)) && is_array($__internal_compile_0) || $__internal_compile_0 instanceof ArrayAccess ? ($__internal_compile_0["attributes"] ?? null) : null);
        // line 2
        if ((twig_get_attribute($this->env, $this->source, ($context["link"] ?? null), "url", [], "array", true, true, true, 2) &&  !twig_test_empty((($__internal_compile_1 = ($context["link"] ?? null)) && is_array($__internal_compile_1) || $__internal_compile_1 instanceof ArrayAccess ? ($__internal_compile_1["url"] ?? null) : null)))) {
            // line 3
            echo "  ";
            $context["tag"] = "a";
        } else {
            // line 5
            echo "  ";
            $context["tag"] = "span";
            // line 6
            echo "  ";
            if (twig_test_empty(($context["submenu"] ?? null))) {
                // line 7
                echo "    ";
                $context["linkAttributes"] = twig_get_attribute($this->env, $this->source, ($context["linkAttributes"] ?? null), "addClass", [0 => "tbm-no-submenu"], "method", false, false, true, 7);
                // line 8
                echo "  ";
            }
        }
        // line 10
        echo "<li ";
        echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(($context["attributes"] ?? null), 10, $this->source), "html", null, true);
        echo " >
  ";
        // line 11
        if ((($context["section"] ?? null) == "frontend")) {
            // line 12
            echo "    <div class=\"tbm-link-container\">
  ";
        }
        // line 14
        echo "    ";
        if ((($context["tag"] ?? null) == "a")) {
            // line 15
            echo "      <";
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(($context["tag"] ?? null), 15, $this->source), "html", null, true);
            echo " href=\"";
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed((($__internal_compile_2 = ($context["link"] ?? null)) && is_array($__internal_compile_2) || $__internal_compile_2 instanceof ArrayAccess ? ($__internal_compile_2["url"] ?? null) : null), 15, $this->source), "html", null, true);
            echo "\" ";
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed((($__internal_compile_3 = ($context["link"] ?? null)) && is_array($__internal_compile_3) || $__internal_compile_3 instanceof ArrayAccess ? ($__internal_compile_3["attributes"] ?? null) : null), 15, $this->source), "html", null, true);
            echo ">
    ";
        } else {
            // line 17
            echo "      <";
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(($context["tag"] ?? null), 17, $this->source), "html", null, true);
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed((($__internal_compile_4 = ($context["link"] ?? null)) && is_array($__internal_compile_4) || $__internal_compile_4 instanceof ArrayAccess ? ($__internal_compile_4["attributes"] ?? null) : null), 17, $this->source), "html", null, true);
            echo ">
    ";
        }
        // line 19
        echo "      ";
        if ((($context["fontawesome"] ?? null) && (($__internal_compile_5 = ($context["item_config"] ?? null)) && is_array($__internal_compile_5) || $__internal_compile_5 instanceof ArrayAccess ? ($__internal_compile_5["xicon"] ?? null) : null))) {
            // line 20
            echo "        <span class=\"";
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed((($__internal_compile_6 = ($context["item_config"] ?? null)) && is_array($__internal_compile_6) || $__internal_compile_6 instanceof ArrayAccess ? ($__internal_compile_6["xicon"] ?? null) : null), 20, $this->source), "html", null, true);
            echo "\"></span>
      ";
        }
        // line 22
        echo "      ";
        echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, ($context["link"] ?? null), "title_translate", [], "any", false, false, true, 22), 22, $this->source), "html", null, true);
        echo "
      ";
        // line 23
        if ((($__internal_compile_7 = ($context["item_config"] ?? null)) && is_array($__internal_compile_7) || $__internal_compile_7 instanceof ArrayAccess ? ($__internal_compile_7["caption"] ?? null) : null)) {
            // line 24
            echo "        ";
            // line 25
            echo "          ";
            // line 26
            echo "          <span class=\"tbm-caption\">";
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar($this->sandbox->ensureToStringAllowed((($__internal_compile_8 = ($context["item_config"] ?? null)) && is_array($__internal_compile_8) || $__internal_compile_8 instanceof ArrayAccess ? ($__internal_compile_8["caption"] ?? null) : null), 26, $this->source));
            echo "</span>
        ";
            // line 28
            echo "      ";
        }
        // line 29
        echo "    </";
        echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(($context["tag"] ?? null), 29, $this->source), "html", null, true);
        echo ">
  ";
        // line 30
        if ((($context["section"] ?? null) == "frontend")) {
            // line 31
            echo "      ";
            if ((($context["submenu"] ?? null) &&  !($context["group"] ?? null))) {
                // line 32
                echo "        <button class=\"tbm-submenu-toggle";
                if ((($__internal_compile_9 = ($context["block_config"] ?? null)) && is_array($__internal_compile_9) || $__internal_compile_9 instanceof ArrayAccess ? ($__internal_compile_9["auto-arrow"] ?? null) : null)) {
                    echo " always-show";
                }
                echo "\"><span class=\"visually-hidden\">";
                echo $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Toggle submenu"));
                echo "</span></button>
      ";
            }
            // line 34
            echo "    </div>
  ";
        }
        // line 36
        echo "  ";
        echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(($context["submenu"] ?? null), 36, $this->source), "html", null, true);
        echo "
</li>
";
    }

    public function getTemplateName()
    {
        return "modules/contrib/tb_megamenu/templates/tb-megamenu-item.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  143 => 36,  139 => 34,  129 => 32,  126 => 31,  124 => 30,  119 => 29,  116 => 28,  111 => 26,  109 => 25,  107 => 24,  105 => 23,  100 => 22,  94 => 20,  91 => 19,  84 => 17,  74 => 15,  71 => 14,  67 => 12,  65 => 11,  60 => 10,  56 => 8,  53 => 7,  50 => 6,  47 => 5,  43 => 3,  41 => 2,  39 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("", "modules/contrib/tb_megamenu/templates/tb-megamenu-item.html.twig", "/app/public_html/modules/contrib/tb_megamenu/templates/tb-megamenu-item.html.twig");
    }
    
    public function checkSecurity()
    {
        static $tags = array("set" => 1, "if" => 2, "autoescape" => 24);
        static $filters = array("escape" => 10, "raw" => 26, "t" => 32);
        static $functions = array();

        try {
            $this->sandbox->checkSecurity(
                ['set', 'if', 'autoescape'],
                ['escape', 'raw', 't'],
                []
            );
        } catch (SecurityError $e) {
            $e->setSourceContext($this->source);

            if ($e instanceof SecurityNotAllowedTagError && isset($tags[$e->getTagName()])) {
                $e->setTemplateLine($tags[$e->getTagName()]);
            } elseif ($e instanceof SecurityNotAllowedFilterError && isset($filters[$e->getFilterName()])) {
                $e->setTemplateLine($filters[$e->getFilterName()]);
            } elseif ($e instanceof SecurityNotAllowedFunctionError && isset($functions[$e->getFunctionName()])) {
                $e->setTemplateLine($functions[$e->getFunctionName()]);
            }

            throw $e;
        }

    }
}
