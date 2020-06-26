<?php declare(strict_types = 1);
//
// Generated by Smalldb\CodeCooker\Generator\DtoGenerator.
// Do NOT edit! All changes will be lost!
// 
// 
namespace Smalldb\StateMachine\Test\Example\SupervisorProcess\SupervisorProcessData;

use Smalldb\CodeCooker\Annotation\GeneratedClass;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\OptionsResolver\OptionsResolver;


/**
 * @GeneratedClass
 * @see \Smalldb\StateMachine\Test\Example\SupervisorProcess\SupervisorProcessProperties
 */
class SupervisorProcessDataFormDataMapper implements DataMapperInterface
{

	public function mapDataToForms($viewData, iterable $forms)
	{
		if ($viewData === null) {
			return;
		} else if ($viewData instanceof SupervisorProcessData) {
			foreach ($forms as $prop => $field) {
				$field->setData(SupervisorProcessDataImmutable::get($viewData, $prop));
			}
		} else {
			throw new UnexpectedTypeException($viewData, SupervisorProcessDataImmutable::class);
		}
	}


	public function mapFormsToData(iterable $forms, & $viewData)
	{
		$viewData = SupervisorProcessDataImmutable::fromIterable($viewData, $forms, function ($field) { return $field->getData(); });
	}


	public function configureOptions(OptionsResolver $optionsResolver)
	{
		$optionsResolver->setDefault("empty_data", null);
		$optionsResolver->setDefault("data_class", SupervisorProcessData::class);
	}

}

