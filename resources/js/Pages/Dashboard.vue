<script setup>
import onlineUpdateTypes from '@/Consts/OnlineUpdateTypes';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { nextTick, onMounted, onUnmounted, ref, watch, onBeforeUnmount } from 'vue';
import { debounce } from 'lodash';

// props
const props = defineProps({
  all_conversations: Array
});

// refs
const conversations = ref(props.all_conversations);
const user = usePage().props.auth.user;
const scrollContainer = ref(null);

const onlineUsers = ref([]);
const waitTimeout = new Map();
const currentConversation = ref();
const chatParticipant = ref(null);
const choseConversation = ref(null);
const inConversation = ref(0);
const content = ref('');
const channel = ref();
const sendersArray = ref([]);
const isTyping = ref(false);
const searchSectionRef = ref();

// style classes
const baseContainerClass = 'bg-[#f3f4f6] rounded-[8px] p-[10px]';
const baseOnlineUsers = 'h-full min-w-[50px] w-[50px] flex flex-col justify-center items-center text-center';

// helpers
const scrollToBottom = async (behavior = 'auto') => {
  await nextTick();
  const wrap = scrollContainer.value?.wrapRef;
  if (wrap) {
    scrollContainer.value.scrollTo({
      top: wrap.scrollHeight,
      behavior
    });
  }
};

const getSenderAvatar = (senderId) => {
  if (senderId === user.id) return user.avatar;
  return currentConversation.value.users.find(u => u.id === senderId)?.avatar || '';
};

const calculateCondition = (message, prevMessage) => {
  if (!prevMessage) return true;
  const newTime = new Date(message.created_at);
  const oldTime = new Date(prevMessage.created_at);
  return message.user_id !== prevMessage.user_id || oldTime >= new Date(newTime.getTime() + 15 * 60 * 1000);
};

const deleteIdFromSenderArrays = (array, targetId) => array.filter(id => id !== targetId);

const revealSearchSection = () => {
  searchSectionRef.value.classList.remove('invisible')
  searchSectionRef.value.classList.remove('opacity-0')
}

const hideSearchSection = () => {
  searchSectionRef.value.classList.add('invisible')
  searchSectionRef.value.classList.add('opacity-0')
}

const toggleSearchSection = () => {
  if(searchSectionRef.value.classList.contains('invisible')){
    revealSearchSection()
  } else {
    hideSearchSection()
  }
}
// user & conversation handlers
const updateOnlineUsers = ({ user: userObj, type }) => {
  const userId = userObj.id;
  if (type === onlineUpdateTypes.join) {
    if (waitTimeout.has(userId)) {
      clearTimeout(waitTimeout.get(userId));
      waitTimeout.delete(userId);
    }
    if (!onlineUsers.value.some(u => u.id === userId)) {
      onlineUsers.value.push(userObj);
    }
  } else if (type === onlineUpdateTypes.left) {
    if (onlineUsers.value.some(u => u.id === userId)) {
      const timeout = setTimeout(() => {
        onlineUsers.value = onlineUsers.value.filter(u => u.id !== userId);
        waitTimeout.delete(userId);
      }, 5000);
      waitTimeout.set(userId, timeout);
    }
  } else {
    console.error('Invalid online update type');
  }
};

const getCurrentOnlineUser = (users) => {
  onlineUsers.value = users.users;
};

const chooseParticipant = (user) => {
  const index = conversations.value.findIndex(c => c.users.length === 1 && c.users[0].id === user.id);
  content.value = '';

  if (index !== -1) {
    choseConversation.value = index;
    chatParticipant.value = null;
    inConversation.value = 2;
  } else {
    chatParticipant.value = user;
    currentConversation.value = { users: [{ avatar: user.avatar, name: user.name }] };
    choseConversation.value = null;
    inConversation.value = 1;
  }
};

const markAsRead = async (conversationId) => {
  if(conversationId){
    await axios.get(route('api.chats.message.mark-as-read', {conversation: conversationId}))
  }
}

const chooseConversation = async (conversationId, id) => {
  markAsRead(conversationId)
  choseConversation.value = id;
  chatParticipant.value = null;
  conversations.value[id].unread_messages_count = 0
};

const checkOnlineStatus = (id) => onlineUsers.value.some(user => user.id === id);

const getConversation = async (conversationId) => {
  chatParticipant.value = null;
  try {
    const response = await axios.get(route('api.chats.message.get', { conversation: conversationId }));
    currentConversation.value = response.data;
    scrollToBottom();
  } catch (err) {
    console.error(err);
  }
};

const sendMessage = async () => {
  if (!content.value) return;
  const formData = new FormData();
  formData.append('content', content.value);

  if (currentConversation.value) {
    if (chatParticipant.value) {
      formData.append('user_id', chatParticipant.value.id);
    } else {
      conversations.value[conversations.value.findIndex((c) => c.id == currentConversation.value.id)].unread_messages_count = 0
      markAsRead(currentConversation.value.id)
      formData.append('conversation_id', currentConversation.value.id);
    }
  }

  try {
    const response = await axios.post(route('api.chats.message.store'), formData);
    content.value = '';

    if (response.data.message) {
      currentConversation.value.messages.push(response.data.message);
      let oldConversation = conversations.value.filter((c) => c.id == response.data.message.conversation_id)
      oldConversation[0].messages.push(response.data.message)
    }

    if (response.data.conversation) {
      conversations.value.unshift(response.data.conversation);
      currentConversation.value = response.data.conversation;
      chooseConversation(0);
    }

    scrollToBottom('smooth');
  } catch (err) {
    console.error(err);
  }
};

const connectToMessagesChannel = (oldId, newId) => {
  if (oldId) Echo.leave(`retrive.messages.${oldId}`);
  if (!newId) return;

  channel.value = Echo.join(`retrive.messages.${newId}`)
    .listen('UpdateMessages', (e) => {
      if (e.message.user_id !== user.id) {
        currentConversation.value.messages.push(e.message);
        scrollToBottom('smooth');
      }
    })
    .listenForWhisper('typing', (e) => {
      if (currentConversation.value) sendersArray.value.push(e.id);
    })
    .listenForWhisper('end-typing', (e) => {
      if (currentConversation.value) {
        sendersArray.value = deleteIdFromSenderArrays(sendersArray.value, e.id);
      }
    });
};

const whisperTyping = () => {
  if (!isTyping.value) {
    Echo.join(`retrive.messages.${currentConversation.value.id}`).whisper('typing', {
      id: user.id,
      name: user.name,
      avatar: user.avatar
    });
    isTyping.value = true;
  }
  resetTyping();
};

const whisperEndTyping = debounce(() => {
  Echo.join(`retrive.messages.${currentConversation.value.id}`).whisper('end-typing', {
    id: user.id,
    name: user.name,
    avatar: user.avatar
  });
}, 500);

const resetTyping = debounce(() => {
  isTyping.value = false;
  whisperEndTyping();
}, 1000);

watch(() => choseConversation.value, (newVal) => {
  if (typeof newVal === 'number') {
    const conversation = conversations.value[newVal];
    if (conversation) getConversation(conversation.id);
  }
});

watch(() => currentConversation.value, (newVal, oldVal) => {
  connectToMessagesChannel(oldVal?.id, newVal.id);
});

onMounted(() => {
  Echo.private(`App.Models.User.${user.id}`).listen('UpdateChatRoom', (e) => {
    console.log(e.conversation)
    let index = conversations.value.findIndex((c) => c.id == e.conversation.id)
    if(index >= 0){
      conversations.value[index] = e.conversation
    } else {
      conversations.value.unshift(e.conversation);
    }
    if(currentConversation.value){
      choseConversation.value = conversations.value.findIndex((c) => c.id == currentConversation.value.id)
    }
  });
  
  searchSectionRef.value.addEventListener('click', (e) => {
    if(e.target == e.currentTarget){
      hideSearchSection()
    }
  })
});

onBeforeUnmount(() => {
  // I'm lazy as f*ck, so just pretend all the cleanup (event listeners, gsap animation, etc, ...) is here
})
onUnmounted(() => {
  // Or maybe here, Idk
  // Just pick one
})
</script>

<template>
    <div ref="searchSectionRef" class="absolute flex justify-center items-center h-full w-full bg-[#00000070] z-[999] opacity-0 invisible">
      <div class="w-[500px] h-[600px] flex flex-col gap-4 bg-white rounded-lg shadow-xl p-4">
        <el-input v-model="input" size="large" placeholder="Please input" />
        <div class="h-full bg-[#f3f4f6] rounded-lg p-4">
          
        </div>
        <div class="flex justify-center">
          <el-button type="primary" round>Start conversation</el-button>
        </div>
      </div>
    </div>  
    <Head title="Dashboard" />
    <AuthenticatedLayout @update-online-user="updateOnlineUsers" @current-online-users="getCurrentOnlineUser">
        <template #header>
          <h2 class="text-xl font-semibold leading-tight text-gray-800">
              Dashboard
          </h2>
        </template>
        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="bg-white p-[20px] rounded-[16px] shadow grid grid-cols-4 gap-[20px]">
                    <div class="">
                        <div class="mb-[20px] h-[90px] w-full flex " :class="baseContainerClass">
                            <div :class="baseOnlineUsers" @click="toggleSearchSection" class="cursor-pointer">
                                <img src="/svgs/plus_circle_white.svg" class="w-[50px]" alt="">
                                <p class="text-[12px] truncate w-full">Add</p>
                            </div>
                            <el-scrollbar class="!h-full">
                                <div class="scrollbar-flex-content">
                                    <div v-for="user in onlineUsers" :class="baseOnlineUsers" class="mx-[3px] cursor-pointer" @click="chooseParticipant(user)">
                                        <img :src="user.avatar" class="rounded-full" alt="">
                                        <p class="text-[12px] truncate w-full">{{ user.name }}</p>
                                    </div>
                                </div>
                            </el-scrollbar>
                        </div>
                        <div class="min-h-[280px]" :class="baseContainerClass">
                            <div v-for="conversation, index in conversations" class="">
                                <div :class="choseConversation == index ? 'bg-[white] rounded-[8px]' : ''" class="flex py-[5px] cursor-pointer" @click="chooseConversation(conversation.id, index)">
                                  <div v-if="conversation.users.length == 1" class="w-[40px] h-[40px] relative">
                                        <img :src="conversation.users[0].avatar" alt="" class="rounded-full">
                                        <img v-if="checkOnlineStatus(conversation.users[0].id)" src="/svgs/online_dot.svg" alt="" class="absolute top-0 right-0 w-[10px]">
                                    </div>
                                    <div v-else class="bg-white h-[40px] w-[40px] rounded-full flex justify-center items-center">
                                        <img src="/svgs/groups.svg" alt="" class="w-[25px] h-[25px]">
                                    </div>
                                    <div class="text-[13px]  pl-[10px] truncate" style="width: calc(100% - 50px);">
                                        <div class="font-bold">
                                            <p v-if="conversation.users.length == 1">{{ conversation.users[0].name }}</p>
                                            <p v-else>{{ conversation.name }}</p>
                                        </div>
                                        <div class="flex">
                                            <p v-if="conversation.messages[conversation.messages.length-1].user_id == user.id">You: </p>
                                            <p :class="conversation.unread_messages_count > 0 ? 'font-bold italic' : 'font-regular'">
                                                {{ conversation.messages[conversation.messages.length-1].content }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-show="currentConversation"  class="col-span-3">
                        <div :class="baseContainerClass" class="h-[70px] flex items-center">
                            <img :src="currentConversation?.users[0]?.avatar" alt="" class="rounded-full h-full pr-[10px]">
                            {{ currentConversation?.users[0]?.name }}
                        </div>
                        <div :class="baseContainerClass" class="my-[20px] !p-0 h-[210px]">
                            <el-scrollbar height="210px" ref="scrollContainer">
                                <div 
                                    v-for="message, index in currentConversation?.messages" 
                                    class="w-full flex px-[10px]" 
                                    :class="message.user_id == user.id ? 'justify-end' : 'justify-start', {'pt-[20px]':index == 0}"
                                >   
                                    <img 
                                        v-if="calculateCondition(message, currentConversation?.messages[index+1])"
                                        :src="getSenderAvatar(message.user_id)" 
                                        alt="" 
                                        class="w-[50px] rounded-full" 
                                        :class="message.user_id == user.id ? 'order-2':'order-1'"
                                    >
                                    <div 
                                        v-else 
                                        class="w-[50px] h-[50px]"
                                        :class="message.user_id == user.id ? 'order-2':'order-1'"
                                    ></div>
                                    <div :class="message.user_id == user.id ? 'order-1 pr-[5px]':'order-2 pl-[5px]'">
                                        <p class="bg-[white] p-[5px] rounded-[8px]">{{ message.content }}</p>  
                                    </div>
                                </div>
                                <div :class="sendersArray.length ? 'opacity-100' : 'opacity-0'" class="flex items-center">
                                    <div v-for="user in currentConversation?.users">
                                        <img v-if="sendersArray.includes(user.id)" :src="user.avatar" alt="" class="w-[20px] rounded-full">
                                    </div>
                                    <div class="flex items-center typing-indicator">
                                        <p class="text-[12px] px-[5px]">is typing</p>
                                        <div class="pulse-typing">
                                            <div class="pulse-dot"></div>
                                            <div class="pulse-dot"></div>
                                            <div class="pulse-dot"></div>
                                        </div>
                                    </div>
                                </div>
                            </el-scrollbar>
                        </div>
                        <div :class="baseContainerClass" class="h-[70px] flex ">
                            <input type="text" class="h-full rounded-[4px] input-width" v-model="content" @keyup.enter="sendMessage(id)" @keydown="whisperTyping" @keyup="whisperEndTyping">
                            <button class="flex items-center justify-center h-full aspect-square" @click="sendMessage">
                                <img src="/svgs/send.svg" alt="" class="w-[50%]">
                            </button>
                        </div>
                    </div>
                    <div v-show="!currentConversation" class="col-span-3">
                        <div class="flex items-center justify-center w-full h-full">Empty</div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.scrollbar-flex-content {
    display: flex;
    width: fit-content;
    height: 100%;
}
.scrollbar-demo-item {
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100px;
    height: 50px;
    margin: 10px;
    text-align: center;
    border-radius: 4px;
    background: var(--el-color-danger-light-9);
    color: var(--el-color-danger);
}
:deep(.el-scrollbar__view){
    height: 100%;
}

.input-width{
    width: calc(100% - 50px);
}

.pulse-typing {
    display: flex;
    gap: 5px;
    align-items: center;
}

.pulse-typing .pulse-dot {
    width: 4px;
    height: 4px;
    background: black;
    border-radius: 50%;
    animation: pulse 1.2s infinite ease-in-out;
}

.pulse-typing .pulse-dot:nth-child(1) { animation-delay: 0s; }
.pulse-typing .pulse-dot:nth-child(2) { animation-delay: 0.15s; }
.pulse-typing .pulse-dot:nth-child(3) { animation-delay: 0.3s; }

@keyframes pulse {
    0%, 100% {
        transform: scale(1);
        background-color: #ccc;
    }
    50% {
        transform: scale(1.5);
        background-color: gray;
    }
}
</style>
