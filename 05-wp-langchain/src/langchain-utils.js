import { OpenAI } from '@langchain/openai';
import { PromptTemplate } from '@langchain/core/prompts';
import { LLMChain } from '@langchain/core/chains';

/**
 * Initialize OpenAI LLM with API key
 * @param {string} apiKey - OpenAI API key
 * @returns {OpenAI} Configured OpenAI instance
 */
export function initOpenAI(apiKey) {
    return new OpenAI({
        openAIApiKey: apiKey,
        modelName: 'gpt-3.5-turbo',
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
 * Create a chain for content generation
 * @param {OpenAI} llm - The language model instance
 * @param {PromptTemplate} prompt - The prompt template
 * @returns {LLMChain} Configured LLM chain
 */
// 

export function createContentChain(llm, prompt) {
    return new LLMChain({
        llm: llm,
        prompt: prompt,
    });
}

/**
 * Generate content using the LangChain
 * @param {LLMChain} chain - The LLM chain
 * @param {Object} inputs - Input parameters for the chain
 * @returns {Promise<string>} Generated content
 */
// *********************************************************************************************

export async function generateContent(chain, inputs) {
    try {
        const result = await chain.call(inputs);
        return result.text;
    } catch (error) {
        console.error('Error generating content:', error);
        throw error;
    }
}
// *********************************************************************************************
