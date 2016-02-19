<?php

namespace SpikeTeam\UserBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * User
 *
 * @ORM\Table(name="fos_user")
 * @ORM\Entity()
 * @UniqueEntity(
 *     fields="email",
 *     message="This email is already signed up."
 * )
 */
class Admin extends BaseUser
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @var string
     *
     * @ORM\Column(name="first_name", type="string", length=60)
     */
    private $firstName;

    /**
     * @var string
     *
     * @ORM\Column(name="last_name", type="string", length=60)
     */
    private $lastName;

    /**
     * @var string
     * @Assert\Regex(
     *     pattern="/[0-9]/",
     *     message="Your phone number must consist only of numbers."
     * )
     *  @Assert\Length(
     *      min = 11,
     *      max = 11,
     *      exactMessage = "Your phone number has an incorrect number of digits.",
     * )
     * @ORM\Column(name="phone_number", type="string", length=11, nullable=true)
     */
    private $phoneNumber;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_enabled", type="boolean", options={"default" = 0})
     */
    private $isEnabled = 0;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set firstName
     *
     * @param string $firstName
     * @return Spiker
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * Get firstName
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Set lastName
     *
     * @param string $lastName
     * @return Spiker
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Get lastName
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    // Override methods for username and tie them with email field
    // from http://stackoverflow.com/questions/10314932/fosuserbundle-login-with-email-symfony2
    /**
     * Sets the email.
     *
     * @param string $email
     * @return User
     */
    public function setEmail($email)
    {
        $this->setUsername($email);
        return parent::setEmail($email);
    }

    /**
     * Set the canonical email.
     *
     * @param string $emailCanonical
     * @return User
     */
    public function setEmailCanonical($emailCanonical)
    {
        $this->setUsernameCanonical($emailCanonical);
        return parent::setEmailCanonical($emailCanonical);
    }

    /**
     * Set phoneNumber
     *
     * @param string $phoneNumber
     * @return Spiker
     */
    public function setPhoneNumber($phoneNumber)
    {
        $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);

        switch (strlen($phoneNumber)) {
            case 10:
                $phoneNumber = '1' . $phoneNumber;
                break;
            case 11:
                $phoneNumber = $phoneNumber;
                break;
        }

        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    /**
     * Get phoneNumber
     *
     * @return string
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * Set isEnabled
     *
     * @param boolean $isEnabled
     * @return Spiker
     */
    public function setIsEnabled($isEnabled)
    {
        $this->isEnabled = $isEnabled;

        return $this;
    }

    /**
     * Get isEnabled
     *
     * @return boolean
     */
    public function getIsEnabled()
    {
        return $this->isEnabled;
    }

    /**
     * Get the highest rated role assigned to this user.
     *
     * @return string
     */
    public function getHighestRole() {
        return $this->getRoles()[0];
    }

    /**
     * Transform array of roles (ROLE_SUPER_ADMIN, ROLE_USER) into a single, user-friendly string
     *
     * @param array $role
     * @return string|boolean
     */
    public function getFriendlyRoleName(){
        //Extract the first role from the roles array, which is the highest role assigned to a user
        $role = $this->getHighestRole();

        $roleMap = array(
            'ROLE_CAPTAIN' => 'Captain',
            'ROLE_ADMIN' => 'Admin',
            'ROLE_SUPER_ADMIN' => 'Super Admin',
        );

        if( isset($roleMap[$role]) ){
            return $roleMap[$role];
        }
        else{
            return false;
        }
    }
}
