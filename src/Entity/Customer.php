<?php

declare(strict_types=1);

namespace Baraja\Shop\Customer\Entity;


use Baraja\EcommerceStandard\DTO\CustomerInterface;
use Baraja\Localization\Localization;
use Baraja\PhoneNumber\PhoneNumberFormatter;
use Doctrine\ORM\Mapping as ORM;
use Nette\Security\Passwords;
use Nette\Utils\Strings;
use Nette\Utils\Validators;

#[ORM\Entity(repositoryClass: CustomerRepository::class)]
#[ORM\Table(name: 'shop__customer')]
class Customer implements CustomerInterface
{
	#[ORM\Id]
	#[ORM\Column(type: 'integer', unique: true, options: ['unsigned' => true])]
	#[ORM\GeneratedValue]
	protected int $id;

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

	#[ORM\Column(type: 'datetime_immutable')]
	private \DateTimeImmutable $insertedDate;


	public function __construct(string $email, string $firstName, string $lastName, ?string $password = null)
	{
		$this->setEmail($email);
		$this->setName($firstName, $lastName);
		if ($password !== null && $password !== '') {
			$this->setPassword($password);
		}
		$this->insertedDate = new \DateTimeImmutable('now');
	}


	public function getId(): int
	{
		assert($this->id >= 1);

		return $this->id;
	}


	public function getName(): string
	{
		return trim(sprintf('%s %s', $this->getFirstName(), $this->getLastName()));
	}


	/**
	 * @return non-empty-string
	 */
	public function getEmail(): string
	{
		if ($this->email === '') {
			throw new \LogicException(sprintf('Customer e-mail can not be empty string (id "%d").', $this->getId()));
		}

		return $this->email;
	}


	public function setEmail(string $email): void
	{
		$email = mb_strtolower(trim($email), 'UTF-8');
		if (Validators::isEmail($email) === false) {
			throw new \InvalidArgumentException(sprintf('Customer e-mail is not valid, because value "%s" given.', $email));
		}
		$this->email = $email;
	}


	/**
	 * @return non-empty-string
	 */
	public function getEmailOrPhone(): string
	{
		return $this->getPhone() ?? $this->getEmail();
	}


	public function getFirstName(): string
	{
		return $this->firstName;
	}


	public function setFirstName(string $firstName): void
	{
		$this->setName($firstName, $this->lastName);
	}


	public function getLastName(): string
	{
		return $this->lastName;
	}


	public function setLastName(string $lastName): void
	{
		$this->setName($this->firstName, $lastName);
	}


	public function getPhone(): ?string
	{
		return $this->phone !== '' ? $this->phone : null;
	}


	public function setPhone(?string $phone, int $region = 420): void
	{
		$phone = trim((string) $phone);
		$this->phone = $phone !== '' ? PhoneNumberFormatter::fix($phone, $region) : null;
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


	public function setLegacyInsertedDate(\DateTimeImmutable $date): void
	{
		$this->insertedDate = $date;
	}


	public function getInsertedDate(): \DateTimeImmutable
	{
		return $this->insertedDate;
	}


	public function getCompanyName(): ?string
	{
		return $this->companyName;
	}


	public function setCompanyName(?string $companyName): void
	{
		$companyName = Strings::firstUpper(trim($companyName ?? ''));
		$this->companyName = $companyName !== '' ? $companyName : null;
	}


	public function getIc(): ?string
	{
		return $this->ic;
	}


	public function setIc(?string $ic): void
	{
		$this->ic = $ic !== '' ? $ic : null;
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
		$street = Strings::firstUpper(trim($street ?? ''));
		$this->street = $street !== '' ? $street : null;
	}


	public function getCity(): ?string
	{
		return $this->city;
	}


	public function setCity(?string $city): void
	{
		$city = Strings::firstUpper(trim($city ?? ''));
		$this->city = $city !== '' ? $city : null;
	}


	public function getZip(): ?int
	{
		return $this->zip;
	}


	public function setZip(?int $zip): void
	{
		if ($zip === 0) {
			$zip = null;
		}
		$this->zip = $zip;
	}


	public function getNote(): ?string
	{
		return $this->note;
	}


	public function setNote(?string $note): void
	{
		$note = trim($note ?? '');
		$this->note = $note !== '' ? $note : null;
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


	/**
	 * Set real user name by first name and last name.
	 * Name can be entered duplicated from wrong source.
	 */
	private function setName(string $firstName, string $lastName): void
	{
		$parts = explode(' ', trim((string) preg_replace('/\s+/', ' ', $firstName)));
		if ($firstName === $lastName && isset($parts[0], $parts[1])) {
			[$firstName, $lastName] = $parts;
		}
		$validator = static function(string $name): void {
			if (trim($name) === '') {
				throw new \InvalidArgumentException('Name can not be empty.');
			}
		};
		$validator($firstName);
		$validator($lastName);
		$this->firstName = $this->formatName($firstName);
		$this->lastName = $this->formatName($lastName);
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
				$name = mb_substr($name, 0, $length, 'UTF-8');
			}
		}

		return Strings::firstUpper($name);
	}
}
