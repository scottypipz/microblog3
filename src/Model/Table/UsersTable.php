<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use Cake\Http\Exception\BadRequestException;
use Cake\Datasource\ConnectionManager;
use SoftDelete\Model\Table\SoftDeleteTrait;

/**
 * Users Model
 *
 * @property \App\Model\Table\CommentsTable&\Cake\ORM\Association\HasMany $Comments
 * @property \App\Model\Table\FollowersTable&\Cake\ORM\Association\HasMany $Followers
 * @property \App\Model\Table\LikesTable&\Cake\ORM\Association\HasMany $Likes
 * @property \App\Model\Table\NotificationsTable&\Cake\ORM\Association\HasMany $Notifications
 * @property \App\Model\Table\PostsTable&\Cake\ORM\Association\HasMany $Posts
 *
 * @method \App\Model\Entity\User get($primaryKey, $options = [])
 * @method \App\Model\Entity\User newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\User[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\User|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\User saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\User patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\User[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\User findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class UsersTable extends Table
{
    use SoftDeleteTrait;
    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->setTable('users');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->hasMany('Comments', [
            'foreignKey' => 'user_id'
        ]);
        $this->hasMany('Followers', [
            'foreignKey' => 'following_id',
            'className' => 'Followers'
        ]);
        $this->hasMany('Following', [
            'foreignKey' => 'user_id',
            'className' => 'Followers'
        ]);
        $this->hasMany('Likes', [
            'foreignKey' => 'user_id'
        ]);
        $this->hasMany('Notifications', [
            'foreignKey' => 'user_id'
        ]);
        $this->hasMany('Posts', [
            'foreignKey' => 'user_id'
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator)
    {
        $validator
            ->integer('id')
            ->allowEmptyString('id', null, 'create');

        $validator
            ->scalar('username')
            ->lengthBetween('username', [6, 20], __('6 to 20 characters only'))
            ->requirePresence('username', 'create')
            ->notEmptyString('username')
            ->add('username', [
                'unique' => [
                    'rule' => 'validateUnique',
                    'provider' => 'table',
                    'message' => 'Username already exists'
                ]
            ]);

        $validator
            ->scalar('first_name')
            ->maxLength('first_name', 70)
            ->requirePresence('first_name', 'create', __('First name is required'))
            ->notEmptyString('first_name', __('First name is required'))
            ->add('first_name', [
                'lettersOnly' => [
                    'rule' => ['custom', '/^[^%#\/*@!0-9]+$/'],
                    'message' => 'Letters only'
                ]
            ]);

        $validator
            ->scalar('last_name')
            ->maxLength('last_name', 35)
            ->requirePresence('last_name', 'create', __('Last name is required'))
            ->notEmptyString('last_name', __('Last name is required'))
            ->add('last_name', [
                'lettersOnly' => [
                    'rule' => ['custom', '/^[^%#\/*@!0-9]+$/'],
                    'message' => 'Letters only'
                ]
            ]);

        $validator
            ->email('email')
            ->requirePresence('email', 'create', __('Email is required'))
            ->notEmptyString('email', __('Email is required'))
            ->add('email', [
                'unique' => [
                    'rule' => 'validateUnique',
                    'provider' => 'table',
                    'message' => 'Email already exists'
                ]
            ]);

        $validator
            ->date('birthdate')
            ->requirePresence('birthdate', 'create', __('Birthdate is required'))
            ->notEmptyDate('birthdate', __('Birthdate is required'))
            ->add('birthdate', [
                'notExceedCurrentDate' => [
                    'rule' => [$this, 'notExceedCurrentDate'],
                    'message' => 'Your birthday should not be greater than today'
                ]
            ]);

        $validator
            ->scalar('password')
            ->maxLength('password', 255)
            ->requirePresence('password', 'create', __('Password is required'))
            ->notEmptyString('password', __('Password is required'));

        $validator
            ->scalar('sex')
            ->allowEmptyString('sex')
            ->requirePresence('sex', 'create', __('Sex is required'))
            ->inList('sex', ['M', 'F'], __('Invalid Sex'))
            ->notEmptyString('sex', __('Sex is required'));

        $validator
            ->scalar('role')
            ->notEmptyString('role');

        $validator
            ->scalar('avatar_url')
            ->allowEmptyString('avatar_url');

        $validator
            ->boolean('is_activated')
            ->notEmptyString('is_activated');

        $validator
            ->scalar('activation_key')
            ->maxLength('activation_key', 255)
            ->requirePresence('activation_key', 'create')
            ->allowEmptyString('activation_key');

        $validator
            ->dateTime('deleted')
            ->allowEmptyDateTime('deleted');
        
        $validator
            ->requirePresence('confirm_password', 'create', __('Confirm password is required'))
            ->add('confirm_password', 'no-misspelling', [
                'rule' => ['compareWith', 'password'],
                'message' => 'Password confirmation does not match password.'
            ]);

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules)
    {
        $rules->add($rules->isUnique(['username']));
        $rules->add($rules->isUnique(['email']));

        return $rules;
    }

    public function fetchByUsername($username, $fields = '*')
    {
        $query = $this->find()
            ->select($fields)
            ->where(['username' => $username]);

        if ($query->isEmpty()) {
            throw new NotFoundException();
        }
        return $query->first();
    }

    /**
     * Adds a new user
     * defaults
     *  is_activated as false
     *  avatar_url as null
     * 
     * @param $data - User data object
     * @return bool
     */
    public function addUser(array $data)
    {
        if (isset($data['is_activated'])) {
            // Someone is manually trying to send request
            throw new BadRequestException();
        }

        $user = $this->newEntity($data);
        $errors = $user->errors();
        if ($errors) {
            return [
                'status' => false,
                'errors' => $errors
            ];
        }
        if ( ! $this->save($user)) {
            throw new InternalErrorException();
        }

        return true;
    }

    /**
     * Updates the is_activated as true account column on Users table
     * 
     * @param string $key - The activation key to match the user
     * @return bool
     */
    public function activateAccount(string $key)
    {
        $user = $this->find()
            ->where(['activation_key' => $key])
            ->first();
        $user->is_activated = b'1';
        if ( ! $this->save($user)) {
            throw new InternalErrorException();
        }
        return true;
    }

    public function fetchFollowers($username)
    {
        $user = $this->find('list')
            ->where(['username' => $username])
            ->contain(['Followers'])
            ->first();
        var_dump($user);die();
    }

    public function fetchRecommendedUsers($userId, $pageNo = 1, $perPage = 5)
    {
        $this->connection = ConnectionManager::get('default');
        $offset = ($pageNo - 1) * $perPage;
        $results = $this->connection->execute(
            'CALL getNotFollowedUsers(?, ?, ?)', 
            [$userId, $perPage, $offset]
        )->fetchAll('assoc');

        if (count($results) === 0) {
            return $this->fetchNotFollowedUsers($userId, $pageNo, $perPage);
        }

        return $results;
    }

    public function fetchNotFollowedUsers($userId, $pageNo = 1, $perPage = 5)
    {
        return $this->find('all' , [
            'conditions' => ['is_activated' => 1, 'id !=' => $userId],
            'order' => 'created DESC',
            'fields' => ['id', 'username', 'first_name', 'last_name', 'avatar_url'],
            'limit' => $perPage,
            'page' => $pageNo
        ])->toList();
    }

    /**
     * Checker for validations
     * TODO put in separate folder
     */
    public function notExceedCurrentDate($value, $context)
    {
        try {
            $dateToCheck = strtotime($context['data']['birthdate']);
            $now = time();
            return $dateToCheck < $now;
        } catch (Exception $e) {
            return false;
        }
    }
}
