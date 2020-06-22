<?php declare(strict_types = 1);
//
// Generated by Smalldb\StateMachine\CodeCooker\Generator\DtoGenerator.
// Do NOT edit! All changes will be lost!
// 
// 
namespace Smalldb\StateMachine\Test\Example\Tag\TagData;

use Smalldb\StateMachine\CodeCooker\Annotation\GeneratedClass;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\OptionsResolver\OptionsResolver;


/**
 * @GeneratedClass
 * @see \Smalldb\StateMachine\Test\Example\Tag\TagProperties
 */
class TagDataFormDataMapper implements DataMapperInterface
{

	public function mapDataToForms($viewData, iterable $forms)
	{
		if ($viewData === null) {
			return;
		} else if ($viewData instanceof TagData) {
			foreach ($forms as $prop => $field) {
				$field->setData(TagDataImmutable::get($viewData, $prop));
			}
		} else {
			throw new UnexpectedTypeException($viewData, TagDataImmutable::class);
		}
	}


	public function mapFormsToData(iterable $forms, & $viewData)
	{
		$viewData = TagDataImmutable::fromIterable($viewData, $forms, function ($field) { return $field->getData(); });
	}


	public function configureOptions(OptionsResolver $optionsResolver)
	{
		$optionsResolver->setDefault("empty_data", null);
		$optionsResolver->setDefault("data_class", TagData::class);
	}

}

