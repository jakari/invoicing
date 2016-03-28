<?php

namespace Invoicing\Bundle\AppBundle\Request\Handler;

use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Xi\Collections\Collection\ArrayCollection;

class ViolationErrorFormatter
{
    /**
     * @param  ConstraintViolationListInterface $violations
     * @return string
     */
    public function violationsToString(
        ConstraintViolationListInterface $violations
    ) {
        return $this->groupViolations($violations)
            ->map(
                function (array $messages) {
                    return ArrayCollection::create($messages)
                        ->map(
                            function ($message) {
                                return  sprintf('"%s"', addslashes($message));
                            }
                        )
                        ->reduce(
                            function ($result, $message) {
                                if (!is_null($result)) {
                                    return sprintf('%s %s', $result, $message);
                                }

                                return $message;
                            }
                        );
                }
            )
            ->reduce(
                function ($result, $messages, $name) {
                    $violations = sprintf('%s: %s', $name, $messages);

                    if ($result) {
                        return sprintf('%s, %s', $result, $violations);
                    }

                    return $violations;
                }
            );
    }

    /**
     * @param  ConstraintViolationListInterface $violations
     * @return ArrayCollection
     */
    private function groupViolations(
        ConstraintViolationListInterface $violations
    ) {
        return ArrayCollection::create($violations)
            ->groupBy(
                function (ConstraintViolationInterface $violation) {
                    return $violation->getPropertyPath();
                }
            )
            ->map(
                function (ArrayCollection $messages) {
                    return $messages
                        ->map(
                            function (ConstraintViolationInterface $violation) {
                                return $violation->getMessage();
                            }
                        )
                        ->toArray();
                }
            );
    }
}
