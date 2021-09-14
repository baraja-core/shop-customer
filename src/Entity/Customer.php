<?php

declare(strict_types=1);

namespace Baraja\Shop\Customer\Entity;


use Baraja\Doctrine\Identifier\IdentifierUnsigned;
use Baraja\Localization\Localization;
use Baraja\PhoneNumber\PhoneNumberFormatter;
use Doctrine\ORM\Mapping as ORM;
use Nette\Security\Passwords;
use Nette\Utils\DateTime;
use Nette\Utils\Strings;
use Nette\Utils\Validators;

#[ORM\Entity]
#[ORM\Table(name: 'shop__customer')]
class Customer
{
	use IdentifierUnsigned;

	#[ORM\Column(type: 'string', length: 128, unique: true)]
	private string $email;

	#[ORM\Column(type: 'string', length: 32)]
	private string $firstName;

	#[ORM\Column(type: 'string', length: 32)]
	private string $lastName;

	#[ORM\Column(type: 'string', length: 32, nullable: true)]
	private ?string $phone = null;

	#[ORM\Column(type: 'string', length: 128, nullable: true)]
	private ?string $password = null;

	#[ORM\Column(type: 'boolean')]
	private bool $newsletter = false;

	#[ORM\Column(type: 'text', nullable: true)]
	private ?string $companyName = null;

	#[ORM\Column(type: 'string', length: 32, nullable: true)]
	private ?string $ic = null;

	#[ORM\Column(type: 'string', length: 32, nullable: true)]
	private ?string $dic = null;

	#[ORM\Column(type: 'string', length: 128, nullable: true)]
	private ?string $street = null;

	#[ORM\Column(type: 'string', length: 64, nullable: true)]
	private ?string $city = null;

	#[ORM\Column(type: 'integer', nullable: true)]
	private ?int $zip = null;

	#[ORM\Column(type: 'text', nullable: true)]
	private ?string $note = null;

	#[ORM\Column(type: 'string', length: 2, nullable: true)]
	private ?string $locale = null;

	#[ORM\Column(type: 'boolean')]
	private bool $premium = false;

	#[ORM\Column(type: 'boolean')]
	private bool $ban = false;

	/**
	 * Total discount as a percentage for each new order.
	 * When creating a new order, the price of the entire order is automatically calculated,
	 * from which this percentage discount is deducted and set as a fixed price per amount.
	 * If the order is further edited, the discount will no longer change and the administrator must edit it manually.
	 */
	#[ORM\Column(type: 'float')]
	private float $defaultOrderSale = 0;

	#[ORM\Column(type: 'integer', nullable: true)]
	private ?int $deprecatedId = null;

	#[ORM\Column(type: 'datetime')]
	private \DateTimeInterface $insertedDate;


	public function __construct(string $email, string $firstName, string $lastName, ?string $password = null)
	{
		$this->setEmail($email);
		$this->setFirstName($firstName);
		$this->setLastName($lastName);
		$password = $password ?: null;
		if ($password !== null) {
			$this->setPassword($password);
		}
		$this->insertedDate = DateTime::from('now');
	}


	public function getName(): string
	{
		return $this->getFirstName() . ' ' . $this->getLastName();
	}


	public function getEmail(): string
	{
		return $this->email;
	}


	public function setEmail(string $email): void
	{
		$email = (string) mb_strtolower($email, 'UTF-8');
		if (Validators::isEmail($email) === false) {
			throw new \InvalidArgumentException(
				'Customer e-mail is not valid, because value "' . $email . '" given.',
			);
		}
		$this->email = $email;
	}


	public function getFirstName(): string
	{
		return $this->firstName;
	}


	public function setFirstName(string $firstName): void
	{
		$this->firstName = $this->formatName($firstName);
	}


	public function getLastName(): string
	{
		return $this->lastName;
	}


	public function setLastName(string $lastName): void
	{
		$this->lastName = $this->formatName($lastName);
	}


	public function getPhone(): ?string
	{
		return $this->phone;
	}


	public function setPhone(?string $phone, int $region = 420): void
	{
		$this->phone = $phone ? PhoneNumberFormatter::fix($phone, $region) : null;
	}


	public function getPassword(): ?string
	{
		return $this->password;
	}


	public function setPassword(string $password): void
	{
		$this->password = (new Passwords)->hash($password);
	}


	public function setLegacyPassword(string $password): void
	{
		$this->password = $password;
	}


	public function passwordVerify(string $password): bool
	{
		if ($this->password === null) {
			return false;
		}

		return (new Passwords)->verify($password, $this->password)
			|| md5($password) === $this->password
			|| sha1(md5($password)) === $this->password;
	}


	public function isNewsletter(): bool
	{
		return $this->newsletter;
	}


	public function setNewsletter(bool $newsletter): void
	{
		$this->newsletter = $newsletter;
	}


	public function setLegacyInsertedDate(\DateTimeInterface $date): void
	{
		$this->insertedDate = $date;
	}


	public function getInsertedDate(): \DateTimeInterface
	{
		return $this->insertedDate;
	}


	public function getCompanyName(): ?string
	{
		return $this->companyName;
	}


	public function setCompanyName(?string $companyName): void
	{
		$this->companyName = Strings::firstUpper(trim($companyName ?? '')) ?: null;
	}


	public function getIc(): ?string
	{
		return $this->ic;
	}


	public function setIc(?string $ic): void
	{
		$this->ic = $ic ?: null;
	}


	public function getDic(): ?string
	{
		return $this->dic;
	}


	public function setDic(?string $dic): void
	{
		$this->dic = $dic;
	}


	public function getStreet(): ?string
	{
		return $this->street;
	}


	public function setStreet(?string $street): void
	{
		$this->street = Strings::firstUpper(trim($street ?? '')) ?: null;
	}


	public function getCity(): ?string
	{
		return $this->city;
	}


	public function setCity(?string $city): void
	{
		$this->city = Strings::firstUpper(trim($city ?? '')) ?: null;
	}


	public function getZip(): ?int
	{
		return $this->zip;
	}


	public function setZip(?int $zip): void
	{
		$this->zip = $zip ?: null;
	}


	public function getNote(): ?string
	{
		return $this->note;
	}


	public function setNote(?string $note): void
	{
		$this->note = trim($note ?? '') ?: null;
	}


	public function getLocale(): ?string
	{
		return $this->locale;
	}


	public function setLocale(?string $locale): void
	{
		if ($locale !== null) {
			$locale = Localization::normalize($locale);
		}
		$this->locale = $locale;
	}


	public function isCompany(): bool
	{
		return $this->getDic() !== null;
	}


	public function isPremium(): bool
	{
		return $this->premium;
	}


	public function setPremium(bool $premium): void
	{
		$this->premium = $premium;
	}


	public function isBan(): bool
	{
		return $this->ban;
	}


	public function setBan(bool $ban): void
	{
		$this->ban = $ban;
	}


	public function getDeprecatedId(): ?int
	{
		return $this->deprecatedId;
	}


	public function setDeprecatedId(?int $deprecatedId): void
	{
		$this->deprecatedId = $deprecatedId;
	}


	public function getDefaultOrderSale(): float
	{
		return $this->defaultOrderSale;
	}


	public function setDefaultOrderSale(float $defaultOrderSale): void
	{
		$this->defaultOrderSale = $defaultOrderSale;
	}


	private function formatName(string $name, int $length = 32): string
	{
		if (mb_strlen($name, 'UTF-8') > $length) {
			$shortName = '';
			foreach (explode(' ', $name) as $part) {
				$tmp = $shortName . ' ' . $part;
				if (mb_strlen($tmp, 'UTF-8') > $length) {
					break;
				}
			}
			$name = trim($shortName);
			if ($name === '') {
				$name = Strings::substring($name, 0, $length);
			}
		}

		return Strings::firstUpper($name);
	}
}
