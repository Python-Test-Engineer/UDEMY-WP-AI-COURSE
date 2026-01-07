import { ChatOpenAI } from '@langchain/openai';
import { PromptTemplate } from '@langchain/core/prompts';

/**
 * Initialize OpenAI LLM with API key
 * @param {string} apiKey - OpenAI API key
 * @returns {ChatOpenAI} Configured ChatOpenAI instance
 */
export function initOpenAI(apiKey) {
    return new ChatOpenAI({
        apiKey: apiKey,
        modelName: 'gpt-4o-mini',
        temperature: 0.7,
    });
}

/**
 * Create a simple prompt template for content generation
 * @returns {PromptTemplate} Configured prompt template
 */
export function createPromptTemplate() {
    return PromptTemplate.fromTemplate(`
Generate a {length} response about {topic} in the style of {style}.

Topic: {topic}
Length: {length}
Style: {style}

Response:`);
}

/**
 * Create a chain for content generation using modern LangChain LCEL (LangChain Expression Language)
 * @param {OpenAI} llm - The language model instance
 * @param {PromptTemplate} prompt - The prompt template
 * @returns {RunnableSequence} Configured chain using pipe
 */
// 

export function createContentChain(llm, prompt) {
    // Modern approach using LCEL - pipe the prompt into the LLM
    return prompt.pipe(llm);
}

/**
 * Generate content using the LangChain
 * @param {RunnableSequence} chain - The chain (prompt piped to LLM)
 * @param {Object} inputs - Input parameters for the chain
 * @returns {Promise<string>} Generated content
 */
// *********************************************************************************************

export async function generateContent(chain, inputs) {
    try {
        const result = await chain.invoke(inputs);
        // ChatOpenAI returns an AIMessage object, extract the content
        return result.content;
    } catch (error) {
        console.error('Error generating content:', error);
        throw error;
    }
}
// *********************************************************************************************
